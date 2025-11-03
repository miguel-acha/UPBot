import { createContext, useContext, useEffect, useMemo, useRef, useState } from "react";
import api from "../api/client";

const AuthContext = createContext(null);
const IDLE_LIMIT = 10 * 60 * 1000; // 10 minutos

export function AuthProvider({ children }) {
  const [token, setToken] = useState(() => localStorage.getItem("token") || "");
  const [user, setUser] = useState(() => {
    try { return JSON.parse(localStorage.getItem("user") || "null"); } catch { return null; }
  });
  const [loading, setLoading] = useState(true);   // cambia a true: cargamos auth inicial
  const [meTried, setMeTried] = useState(false);  // sabemos si intentamos /me al menos una vez

  // ---- Inactividad ----
  const idleTimerRef = useRef(null);
  const lastActiveRef = useRef(Date.now());

  function clearIdleTimer() {
    if (idleTimerRef.current) {
      clearTimeout(idleTimerRef.current);
      idleTimerRef.current = null;
    }
  }

  function startIdleTimer() {
    clearIdleTimer();
    idleTimerRef.current = setTimeout(() => {
      const elapsed = Date.now() - lastActiveRef.current;
      if (elapsed >= IDLE_LIMIT) {
        logout(true);
      } else {
        startIdleTimer();
      }
    }, IDLE_LIMIT);
  }

  function markActivity() {
    lastActiveRef.current = Date.now();
    startIdleTimer();
  }

  // Attach listeners solo si hay token
  useEffect(() => {
    if (!token) return;
    const events = ["mousemove", "mousedown", "keydown", "touchstart", "scroll"];
    events.forEach((ev) => window.addEventListener(ev, markActivity, { passive: true }));
    startIdleTimer();
    return () => {
      events.forEach((ev) => window.removeEventListener(ev, markActivity));
      clearIdleTimer();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [token]);

  // Hidratación inicial: si hay token, trae /me; si /me falla => logout
  useEffect(() => {
    (async () => {
      try {
        if (token && token !== "null" && token !== "undefined") {
          const { data } = await api.get("/me");
          setUser(data);
          localStorage.setItem("user", JSON.stringify(data));
        } else {
          setUser(null);
        }
      } catch (err) {
        // SOLO si /me falla, cerramos sesión
        console.error("Hydrate /me error:", err?.response?.status, err);
        logout();
        return;
      } finally {
        setMeTried(true);
        setLoading(false);
      }
    })();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []); // una vez

  async function login(email, password) {
    setLoading(true);
    try {
      const { data } = await api.post("/login", { email, password });
      const { token: t, user: u } = data || {};
      if (!t) {
        return { ok: false, message: "Token inválido" };
      }
      // 1) Persistir token + user inmediato (u ya trae role)
      localStorage.setItem("token", t);
      setToken(t);

      if (u) {
        setUser(u);
        localStorage.setItem("user", JSON.stringify(u));
      }

      markActivity();

      // 2) (opcional) refrescar /me sin botar al usuario si falla
      try {
        const me = await api.get("/me");
        setUser(me.data);
        localStorage.setItem("user", JSON.stringify(me.data));
      } catch (e) {
        console.warn("Refresh /me falló; se mantiene el user de login", e?.response?.status);
      }

      return { ok: true };
    } catch (err) {
      const msg =
        err?.response?.data?.message ||
        "No se pudo iniciar sesión. Verifica tus datos.";
      return { ok: false, message: msg };
    } finally {
      setLoading(false);
    }
  }

  function logout(byIdle = false) {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    setToken("");
    setUser(null);
    clearIdleTimer();
    if (!location.pathname.startsWith("/login")) {
      location.href = "/login";
    }
  }

  const value = useMemo(
    () => ({ token, user, loading, meTried, login, logout }),
    [token, user, loading, meTried]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  return useContext(AuthContext);
}
