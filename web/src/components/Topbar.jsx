// src/components/Topbar.jsx
import { useAuth } from "../context/AuthContext";

export default function Topbar() {
  const { user, logout } = useAuth();

  return (
    <div className="topbar">
      <div className="topbar-inner">
        <div className="brand">UPBot</div>
        <div className="topbar-right">
          {user?.email && (
            <span className="small muted" title={user.email}>
              {user.email}
            </span>
          )}
          <button className="btn" onClick={() => logout(false)}>
            Cerrar sesión
          </button>
        </div>
      </div>
    </div>
  );
}
