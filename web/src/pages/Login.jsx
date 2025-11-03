import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../context/AuthContext";

const ROUTE_BY_ROLE = {
  admin: "/admin",
  head_of_program: "/jefe-carrera",
  teacher: "/profesor",
  student: "/mis-consultas",
};

export default function Login() {
  const navigate = useNavigate();
  const { token, loading, login, user, meTried } = useAuth();

  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [showPwd, setShowPwd] = useState(false);
  const [error, setError] = useState("");

  // Redirección por rol cuando ya tienes token + user
  useEffect(() => {
    if (loading || !meTried) return;
    if (!token || token === "null" || token === "undefined") return;
    if (!user) return;

    const role = String(user.role || user.roles?.[0] || "").toLowerCase();
    const target = ROUTE_BY_ROLE[role] || "/mis-consultas";
    if (window.location.pathname !== target) {
      navigate(target, { replace: true });
    }
  }, [loading, meTried, token, user, navigate]);

  async function onSubmit(e) {
    e.preventDefault();
    setError("");

    const { ok, message } = await login(email.trim(), password);
    if (!ok) {
      setError(message || "No se pudo iniciar sesión");
    }
  }

  return (
    <div className="screen">
      <div className="bg-blob" />
      <div className="bg-blob b2" />

      <div className="card login-card hoverable" style={{ textAlign: "left" }}>
        <h1 className="h2" style={{ textAlign: "center", marginBottom: 4 }}>Iniciar Sesión</h1>
        <p className="small muted" style={{ textAlign: "center", marginTop: 0 }}>
          Accede con tu correo <span className="strong">@upb.edu</span>
        </p>

        {error && <div className="error">{error}</div>}

        <form className="form" onSubmit={onSubmit} autoComplete="on">
          {/* Email */}
          <div>
            <label className="label" htmlFor="email">Correo</label>
            <div className="field">
              <span className="icon-left" aria-hidden>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5L4 8V6l8 5 8-5v2Z"/>
                </svg>
              </span>
              <input
                id="email"
                type="email"
                inputMode="email"
                placeholder="alumno@upb.edu"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
              />
            </div>
          </div>

          {/* Password */}
          <div>
            <label className="label" htmlFor="password">Contraseña</label>
            <div className="field">
              <span className="icon-left" aria-hidden>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M17 8h-1V6a4 4 0 1 0-8 0v2H7a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2Zm-8-2a3 3 0 1 1 6 0v2H9V6Zm8 12H7v-8h10v8Z"/>
                </svg>
              </span>
              <input
                id="password"
                type={showPwd ? "text" : "password"}
                placeholder="********"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
              />
              <button
                type="button"
                className="ghost-btn eye-btn"
                aria-label={showPwd ? "Ocultar contraseña" : "Ver contraseña"}
                onClick={() => setShowPwd((v) => !v)}
              >
                {showPwd ? (
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Zm10 4a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/>
                  </svg>
                ) : (
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="m3 3 18 18-1.41 1.41L16.9 19.72A10.75 10.75 0 0 1 12 21C5.5 21 2 12 2 12a19.1 19.1 0 0 1 5.18-6.85L1.59 4.41 3 3Zm7.73 7.73A3 3 0 0 0 12 15a3 3 0 0 0 2.27-4.95l-1.4 1.4a1 1 0 1 1-1.41-1.41l1.27-1.27ZM12 3c6.5 0 10 9 10 9a19.2 19.2 0 0 1-4.38 6.2l-1.43-1.43A10.74 10.74 0 0 0 21 12s-3.5-7-9-7c-1.16 0-2.25.23-3.26.63L7.1 4.1A12.5 12.5 0 0 1 12 3Z"/>
                  </svg>
                )}
              </button>
            </div>
          </div>

          <button className="btn btn-primary" type="submit" disabled={loading}>
            {loading ? "Ingresando..." : "Entrar"}
          </button>
        </form>
      </div>
    </div>
  );
}
