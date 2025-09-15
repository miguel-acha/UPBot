import { createContext, useContext, useEffect, useMemo, useRef, useState } from "react";
import api from "../api/client";

const AuthContext = createContext(null);
const IDLE_LIMIT = 10 * 60 * 1000; // 10 minutos

export function AuthProvider({ children }) {
  const [token, setToken] = useState(() => localStorage.getItem("token") || "");
  const [user, setUser] = useState(() => {
    try { return JSON.parse(localStorage.getItem("user") || "null"); } catch { return null; }
  });
  const [loading, setLoading] = useState(false);

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

  useEffect(() => {
    if (token && !user) {
      (async () => {
        try {
          const { data } = await api.get("/me");
          setUser(data);
          localStorage.setItem("user", JSON.stringify(data));
        } catch {
          logout();
        }
      })();
    }
  }, [token]); // eslint-disable-line

  async function login(email, password) {
    setLoading(true);
    try {
      const { data } = await api.post("/login", { email, password });
      const { token: t, user: u } = data;
      localStorage.setItem("token", t);
      localStorage.setItem("user", JSON.stringify(u));
      setToken(t);
      setUser(u);
      markActivity();
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
    () => ({ token, user, loading, login, logout }),
    [token, user, loading]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  return useContext(AuthContext);
}
