import { Navigate, Outlet, useLocation } from "react-router-dom";
import { useAuth } from "../context/AuthContext";

function CenterLoader() {
  return (
    <div className="screen">
      <div className="bg-blob" />
      <div className="bg-blob b2" />
      <div className="card login-card hoverable" style={{ textAlign: "center" }}>
        <div className="loading-list" style={{ marginTop: 0 }}>
          <div className="skel-card" />
          <div className="skel-card" />
        </div>
        <p className="small muted" style={{ marginTop: 12 }}>Cargando…</p>
      </div>
    </div>
  );
}

export default function ProtectedRoute({ roles }) {
  const { token, loading, user, meTried } = useAuth();
  const location = useLocation();

  // Esperar la hidratación inicial
  if (loading || !meTried) return <CenterLoader />;

  // Sin token -> login
  if (!token || token === "null" || token === "undefined") {
    return <Navigate to="/login" replace state={{ from: location }} />;
  }

  // Validación de roles si aplica
  if (roles && roles.length) {
    const role = String(user?.role || user?.roles?.[0] || "").toLowerCase();
    if (!role) return <CenterLoader />; // evita flicker si aún no llega el role
    if (!roles.includes(role)) {
      return <Navigate to="/mis-consultas" replace />;
    }
  }

  return <Outlet />;
}
