import { useState, useEffect, useMemo } from "react";
import api from "../api/client";
import Topbar from "../components/Topbar";
import { useAuth } from "../context/AuthContext";

export default function AdminDashboard() {
  const { user } = useAuth();
  const role = useMemo(
    () => String(user?.role || user?.roles?.[0] || "").toLowerCase(),
    [user]
  );

  const [email, setEmail] = useState("");
  const [name, setName] = useState("");
  const [ci, setCi] = useState("");
  const [password, setPassword] = useState("UPB-2025");
  const [roleToCreate, setRoleToCreate] = useState("student");

  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const [ok, setOk] = useState(false);
  const [showNotification, setShowNotification] = useState(false);
  const [successText, setSuccessText] = useState("Usuario creado correctamente.");

  const handleSubmit = async (e) => {
    e.preventDefault();

    const emailNorm = email.trim().toLowerCase();
    const nameNorm = name.trim();
    const ciNorm = ci.trim();
    const pwd = String(password || "").trim();
    const roleNorm = String(roleToCreate || "").toLowerCase();

    if (!emailNorm.endsWith("@upb.edu")) {
      setError("El correo debe terminar en @upb.edu");
      setShowNotification(true);
      return;
    }
    if (!nameNorm) {
      setError("El nombre es obligatorio");
      setShowNotification(true);
      return;
    }
    if (pwd.length < 8) {
      setError("La contraseña debe tener al menos 8 caracteres");
      setShowNotification(true);
      return;
    }

    setError("");
    setOk(false);
    setLoading(true);

    try {
      let resp;
      if (roleNorm === "student") {
        resp = await api.post("/admin/student-user", {
          email: emailNorm,
          name: nameNorm,
          password: pwd,
          student: { full_name: nameNorm, ci: ciNorm || null },
        });
      } else {
        resp = await api.post("/users", {
          email: emailNorm,
          name: nameNorm,
          password: pwd,
          role: roleNorm, // teacher | head_of_program | admin
          is_active: true,
        });
      }

      setOk(true);
      setSuccessText(resp?.data?.message || "Usuario creado correctamente.");
      setShowNotification(true);

      setEmail("");
      setName("");
      setCi("");
      setPassword("UPB-2025");
      setRoleToCreate("student");
    } catch (err) {
      const errorMsg =
        err?.response?.data?.message ||
        (err?.response?.data?.errors
          ? Object.values(err.response.data.errors).flat().join(" ")
          : null) ||
        err?.message ||
        "No se pudo crear el usuario.";
      setError(errorMsg);
      setShowNotification(true);
      console.error("Error al crear usuario:", err, err?.response?.data);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (showNotification) {
      const timer = setTimeout(() => {
        setShowNotification(false);
        setError("");
        setOk(false);
      }, 4000);
      return () => clearTimeout(timer);
    }
  }, [showNotification]);

  const closeNotification = () => {
    setShowNotification(false);
    setError("");
    setOk(false);
  };

  // --- Autorización a nivel de componente ---
  if (role && role !== "admin") {
    return (
      <>
        <Topbar />
        <div className="screen">
          <div className="bg-blob" />
          <div className="bg-blob b2" />
          <div className="card login-card hoverable" style={{ textAlign: "center" }}>
            <h1 className="h2">No autorizado</h1>
            <p className="small muted">Esta sección es solo para administradores.</p>
          </div>
        </div>
      </>
    );
  }

  return (
    <>
      <Topbar />

      {showNotification && (error || ok) && (
        <div
          style={{
            position: "fixed",
            top: "70px",
            right: "24px",
            zIndex: 1000,
            minWidth: "340px",
            maxWidth: "400px",
            background: error
              ? "linear-gradient(135deg, #ff4757 0%, #ff3838 100%)"
              : "linear-gradient(135deg, #2ed573 0%, #1dd1a1 100%)",
            color: "#fff",
            padding: "18px 22px",
            borderRadius: "12px",
            boxShadow:
              "0 10px 40px rgba(0,0,0,0.25), 0 4px 12px rgba(0,0,0,0.15)",
            animation:
              "slideInRight 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards",
            display: "flex",
            alignItems: "center",
            gap: "14px",
            fontFamily:
              "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
            border: "1px solid rgba(255,255,255,0.1)",
          }}
        >
          <div style={{ flexShrink: 0 }}>
            {error ? (
              <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zM13 17h-2v-2h2v2zm0-4h-2V7h2v6z"/>
              </svg>
            ) : (
              <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
              </svg>
            )}
          </div>
          <div style={{ flex: 1 }}>
            <div style={{ fontWeight: "700", fontSize: "15px", marginBottom: "3px" }}>
              {error ? "Error" : "¡Éxito!"}
            </div>
            <div style={{ fontSize: "13px", opacity: "0.95", lineHeight: "1.5" }}>
              {error || successText}
            </div>
          </div>
          <button
            onClick={closeNotification}
            style={{
              background: "rgba(255,255,255,0.15)",
              border: "none",
              borderRadius: "8px",
              padding: "8px",
              color: "#fff",
              cursor: "pointer",
              display: "flex",
              alignItems: "center",
              justifyContent: "center",
              transition: "all 0.2s ease",
              opacity: "0.8",
            }}
            aria-label="Cerrar notificación"
          >
            ✕
          </button>
        </div>
      )}

      <div className="screen">
        <div className="bg-blob" />
        <div className="bg-blob b2" />
        <div className="card login-card hoverable" style={{ textAlign: "left" }}>
          <h1 className="h2" style={{ textAlign: "center", marginBottom: 4 }}>Panel Administrador</h1>
          <p className="small muted" style={{ textAlign: "center", marginTop: 0 }}>
            Crear nuevo usuario con correo <span className="strong">@upb.edu</span>
          </p>

          <form className="form" onSubmit={handleSubmit} autoComplete="off">
            {/* Correo */}
            <div>
              <label className="label" htmlFor="email">Correo</label>
              <div className="field">
                <span className="icon-left" aria-hidden>
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5L4 8V6l8 5 8-5v2Z"/>
                  </svg>
                </span>
                <input
                  id="email"
                  type="email"
                  placeholder="usuario@upb.edu"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  required
                />
              </div>
            </div>

            {/* Nombre */}
            <div>
              <label className="label" htmlFor="name">Nombre</label>
              <div className="field">
                <span className="icon-left" aria-hidden>
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 12c2.7 0 5-2.3 5-5S14.7 2 12 2 7 4.3 7 7s2.3 5 5 5zm0 2c-3.3 0-10 1.7-10 5v3h20v-3c0-3.3-6.7-5-10-5z"/>
                  </svg>
                </span>
                <input
                  id="name"
                  type="text"
                  placeholder="Nombre completo"
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                  required
                />
              </div>
            </div>

            {/* CI (opcional) */}
            <div>
              <label className="label" htmlFor="ci">Carnet de Identidad </label>
              <div className="field">
                <span className="icon-left" aria-hidden>
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 14H4V8h16v10zM6 10h5v2H6v-2z"/>
                  </svg>
                </span>
                <input
                  id="ci"
                  type="text"
                  placeholder="0000000 SC"
                  value={ci}
                  onChange={(e) => setCi(e.target.value)}
                />
              </div>
            </div>

            {/* Rol a crear */}
            <div>
              <label className="label" htmlFor="role">Rol</label>
              <div className="field">
                <span className="icon-left" aria-hidden>
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2l7 4v6c0 5-3.8 9.4-7 10-3.2-.6-7-5-7-10V6l7-4z"/>
                  </svg>
                </span>
                <select
                  id="role"
                  value={roleToCreate}
                  onChange={(e) => setRoleToCreate(e.target.value)}
                  required
                >
                  <option value="student">Estudiante</option>
                  <option value="teacher">Docente</option>
                  <option value="head_of_program">Jefe de carrera</option>
                  <option value="admin">Administrador</option>
                </select>
              </div>
            </div>

            {/* Contraseña */}
            <div>
              <label className="label" htmlFor="password">Contraseña inicial</label>
              <div className="field">
                <span className="icon-left" aria-hidden>
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17 8h-1V6a4 4 0 1 0-8 0v2H7a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2Z"/>
                  </svg>
                </span>
                <input
                  id="password"
                  type="text"
                  placeholder="UPB-2025"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                />
              </div>
            </div>

            <button className="btn btn-primary" type="submit" disabled={loading}>
              {loading ? "Creando..." : "Crear usuario"}
            </button>
          </form>
        </div>
      </div>
    </>
  );
}
