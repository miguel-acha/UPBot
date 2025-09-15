import { useState } from 'react';
import api from '../api/client';
import { useAuth } from '../context/AuthContext';
import Layout from '../components/Layout';

export default function ChangePassword() {
  const { user, setUser, logout } = useAuth();
  const [current_password, setCurrent] = useState('');
  const [new_password, setNew] = useState('');
  const [new_password_confirmation, setNew2] = useState('');
  const [msg, setMsg] = useState('');
  const [err, setErr] = useState('');
  const [loading, setLoading] = useState(false);

  const submit = async (e) => {
    e.preventDefault();
    setMsg(''); setErr(''); setLoading(true);
    try {
      await api.post('/me/password', { current_password, new_password, new_password_confirmation });
      setUser({ ...user, must_change_password: false });
      setMsg('Contraseña actualizada correctamente. Ya puedes usar el portal.');
      setCurrent(''); setNew(''); setNew2('');
    } catch (e) {
      setErr(e?.response?.data?.message || 'No se pudo actualizar');
    } finally {
      setLoading(false);
    }
  };

  return (
    <Layout>
      <div className="max-w-lg mx-auto">
        <div className="card">
          <h1 className="text-xl font-semibold mb-2">Cambiar contraseña</h1>
          <p className="text-sm text-slate-500 mb-4">
            Por seguridad debes actualizar tu contraseña antes de continuar.
          </p>
          <form onSubmit={submit} className="space-y-3">
            <div>
              <label className="label">Contraseña actual</label>
              <input className="input" type="password" value={current_password} onChange={e=>setCurrent(e.target.value)} required />
            </div>
            <div>
              <label className="label">Nueva contraseña</label>
              <input className="input" type="password" value={new_password} onChange={e=>setNew(e.target.value)} required />
            </div>
            <div>
              <label className="label">Confirmación</label>
              <input className="input" type="password" value={new_password_confirmation} onChange={e=>setNew2(e.target.value)} required />
            </div>
            {err && <div className="text-sm text-red-600">{err}</div>}
            {msg && <div className="text-sm text-green-700">{msg}</div>}
            <button disabled={loading} className="btn btn-primary w-full">
              {loading ? 'Actualizando…' : 'Actualizar'}
            </button>
          </form>
          <button onClick={logout} className="btn btn-ghost w-full mt-3">Cerrar sesión</button>
        </div>
      </div>
    </Layout>
  );
}
