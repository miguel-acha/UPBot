import { useAuth } from '../context/AuthContext';
import { LogOut } from 'lucide-react';
import { Link } from 'react-router-dom';

export default function Layout({ children }) {
  const { user, logout } = useAuth();

  return (
    <div className="page">
      <nav className="navbar">
        <div className="navbar-inner">
          <Link to="/mis-consultas" className="brand">UPBot · Portal</Link>
          <div className="flex items-center gap-3">
            {user && (
              <>
                <div className="text-sm text-slate-700">
                  {user.name || user.email}
                </div>
                <button onClick={logout} className="btn btn-ghost" title="Cerrar sesión">
                  <LogOut size={18} />
                </button>
              </>
            )}
          </div>
        </div>
      </nav>
      <main className="container-page py-6">
        {children}
      </main>
      <footer className="border-t mt-10">
        <div className="container-page py-6 text-xs text-slate-500">
          © {new Date().getFullYear()} UPB — Portal de consultas.
        </div>
      </footer>
    </div>
  );
}
