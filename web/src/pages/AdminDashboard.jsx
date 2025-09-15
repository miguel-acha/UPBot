import { useState, useEffect } from "react";
import { useAuth } from "../context/AuthContext";
import api from "../api/client";
import Topbar from "../components/Topbar";

export default function AdminDashboard() {
  const [email, setEmail] = useState("");
  const [name, setName] = useState("");
  const [password, setPassword] = useState("UPB-2025");
  const [error, setError] = useState("");
  const [loading, setLoading] = useState(false);
  const [ok, setOk] = useState(false);
  const [showNotification, setShowNotification] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    // Validar que el email termine en @upb.edu
    if (!email.trim().toLowerCase().endsWith('@upb.edu')) {
      setError("El correo debe terminar en @upb.edu");
      setShowNotification(true);
      return;
    }

    setError("");
    setOk(false);
    setLoading(true);
    try {
      await api.post("/users", {
        email,
        name,
        password,
        password_confirmation: password,
        role: "student",
        is_active: 1
      });
      setOk(true);
      setShowNotification(true);
      setEmail("");
      setName("");
      setPassword("UPB-2025");
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

  return (
    <>
      <Topbar />
      
      {/* Toast Notification - Alineado con topbar */}
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
            boxShadow: "0 10px 40px rgba(0,0,0,0.25), 0 4px 12px rgba(0,0,0,0.15)",
            animation: "slideInRight 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards",
            display: "flex",
            alignItems: "center",
            gap: "14px",
            fontFamily: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
            border: "1px solid rgba(255,255,255,0.1)"
          }}
        >
          {/* Icono */}
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
          
          {/* Contenido */}
          <div style={{ flex: 1 }}>
            <div style={{ 
              fontWeight: "700", 
              fontSize: "15px", 
              marginBottom: "3px"
            }}>
              {error ? "Error" : "¡Éxito!"}
            </div>
            <div style={{ 
              fontSize: "13px", 
              opacity: "0.95",
              lineHeight: "1.5"
            }}>
              {error || "Usuario creado correctamente."}
            </div>
          </div>
          
          {/* Botón cerrar */}
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
              opacity: "0.8"
            }}
            onMouseOver={(e) => {
              e.target.style.background = "rgba(255,255,255,0.25)";
              e.target.style.opacity = "1";
            }}
            onMouseOut={(e) => {
              e.target.style.background = "rgba(255,255,255,0.15)";
              e.target.style.opacity = "0.8";
            }}
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
            </svg>
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
                  placeholder="usuario@upb.edu"
                  value={email}
                  onChange={e => setEmail(e.target.value)}
                  required
                />
              </div>
            </div>
            <div>
              <label className="label" htmlFor="name">Nombre</label>
              <div className="field">
                <span className="icon-left" aria-hidden>
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C13.1 2 14 2.9 14 4C14 5.1 13.1 6 12 6C10.9 6 10 5.1 10 4C10 2.9 10.9 2 12 2ZM21 9V7L15 4V6C15 7.1 14.1 8 13 8H11C9.9 8 9 7.1 9 6V4L3 7V9H21ZM12 17.5L16.5 13H7.5L12 17.5Z"/>
                  </svg>
                </span>
                <input
                  id="name"
                  type="text"
                  placeholder="Nombre completo"
                  value={name}
                  onChange={e => setName(e.target.value)}
                  required
                />
              </div>
            </div>
            <div>
              <label className="label" htmlFor="password">Contraseña inicial</label>
              <div className="field">
                <span className="icon-left" aria-hidden>
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17 8h-1V6a4 4 0 1 0-8 0v2H7a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2Zm-8-2a3 3 0 1 1 6 0v2H9V6Zm8 12H7v-8h10v8Z"/>
                  </svg>
                </span>
                <input
                  id="password"
                  type="text"
                  placeholder="UPB-2025"
                  value={password}
                  onChange={e => setPassword(e.target.value)}
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
