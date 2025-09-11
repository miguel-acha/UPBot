import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import api from '../api/client';
import { useAuth } from '../context/AuthContext';

export default function MyResponses() {
  const { user, logout } = useAuth();
  const [items, setItems] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    api.get('/my/responses')
      .then(res => {
        // soporta formato {data:[...]} o array directo
        setItems(res.data?.data || res.data || []);
      })
      .catch(() => setError('No se pudo cargar la lista'))
      .finally(() => setLoading(false));
  }, []);

  return (
    <div style={{maxWidth:900, margin:'0 auto', padding:24}}>
      <header style={{display:'flex', justifyContent:'space-between', marginBottom:24}}>
        <h1 style={{fontSize:24, fontWeight:600}}>Mis consultas</h1>
        <div style={{display:'flex', alignItems:'center', gap:12}}>
          <span style={{fontSize:13, color:'#475569'}}>{user?.email}</span>
          <button onClick={logout} style={{fontSize:13, color:'#2563eb'}}>Salir</button>
        </div>
      </header>

      {loading && <div>Cargando…</div>}
      {error && <div style={{color:'#dc2626', marginBottom:12}}>{error}</div>}

      <ul style={{display:'grid', gap:12}}>
        {items.map(r => (
          <li key={r.id} style={{border:'1px solid #e5e7eb', borderRadius:12, padding:16}}>
            <div style={{display:'flex', justifyContent:'space-between', alignItems:'center'}}>
              <div>
                <p style={{fontWeight:600}}>{r.summary || 'Respuesta'}</p>
                <p style={{fontSize:13, color:'#64748b'}}>Tipo: {r.payload_type || r.type}</p>
              </div>
              <Link to={`/mis-consultas/${r.id}`} style={{color:'#2563eb'}}>Ver</Link>
            </div>
          </li>
        ))}
      </ul>

      {!loading && items.length === 0 && (
        <div style={{color:'#475569', marginTop:12}}>No tienes registros aún.</div>
      )}
    </div>
  );
}
