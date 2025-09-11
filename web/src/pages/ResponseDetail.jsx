import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import api from '../api/client';

export default function ResponseDetail() {
  const { id } = useParams();
  const [data, setData] = useState(null);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.get(`/my/responses/${id}`)
      .then(res => setData(res.data))
      .catch(() => setError('No se pudo cargar el detalle'))
      .finally(() => setLoading(false));
  }, [id]);

  return (
    <div style={{maxWidth:900, margin:'0 auto', padding:24}}>
      <div style={{display:'flex', justifyContent:'space-between', alignItems:'center'}}>
        <h1 style={{fontSize:24, fontWeight:600}}>Detalle</h1>
        <Link to="/mis-consultas" style={{color:'#2563eb'}}>Volver</Link>
      </div>

      {loading && <div>Cargando…</div>}
      {error && <div style={{color:'#dc2626'}}>{error}</div>}

      {data && (
        <div style={{border:'1px solid #e5e7eb', borderRadius:12, padding:16, marginTop:12}}>
          <p style={{fontWeight:600, marginBottom:8}}>{data.summary || 'Respuesta'}</p>
          <p style={{fontSize:13, color:'#64748b', marginBottom:8}}>Tipo: {data.type || data.payload_type}</p>
          {data.data && (
            <pre style={{background:'#f8fafc', padding:12, borderRadius:8, overflow:'auto', fontSize:13}}>
              {JSON.stringify(data.data, null, 2)}
            </pre>
          )}
          {data.document_id && (
            <div style={{marginTop:8, fontSize:13}}>Documento asociado ID: {data.document_id}</div>
          )}
        </div>
      )}
    </div>
  );
}
