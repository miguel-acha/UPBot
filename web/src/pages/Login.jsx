import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';

export default function Login() {
  const navigate = useNavigate();
  const { login } = useAuth();
  const [email, setEmail] = useState('alumno1@upb.edu');
  const [password, setPassword] = useState('Alumno123!');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      await login(email, password);
      navigate('/mis-consultas');
    } catch (err) {
      setError(err?.response?.data?.message || 'Error de autenticación');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div style={{minHeight:'100vh',display:'grid',placeItems:'center',background:'#f6f7fb'}}>
      <div style={{width:360, background:'#fff', border:'1px solid #e5e7eb', borderRadius:12, padding:24}}>
        <h1 style={{fontSize:20, fontWeight:600, marginBottom:12}}>Iniciar sesión</h1>
        <form onSubmit={handleSubmit} style={{display:'grid', gap:12}}>
          <div>
            <label style={{fontSize:12, display:'block', marginBottom:6}}>Email</label>
            <input style={{width:'100%', border:'1px solid #e5e7eb', borderRadius:8, padding:'8px 12px'}}
                   type="email" value={email} onChange={(e)=>setEmail(e.target.value)} required />
          </div>
          <div>
            <label style={{fontSize:12, display:'block', marginBottom:6}}>Contraseña</label>
            <input style={{width:'100%', border:'1px solid #e5e7eb', borderRadius:8, padding:'8px 12px'}}
                   type="password" value={password} onChange={(e)=>setPassword(e.target.value)} required />
          </div>
          {error && <div style={{color:'#dc2626', fontSize:13}}>{error}</div>}
          <button disabled={loading}
                  style={{width:'100%', background:'#2563eb', color:'#fff', borderRadius:8, padding:'10px 12px', fontWeight:600, opacity:loading?0.7:1}}>
            {loading ? 'Ingresando…' : 'Ingresar'}
          </button>
        </form>
      </div>
    </div>
  );
}
