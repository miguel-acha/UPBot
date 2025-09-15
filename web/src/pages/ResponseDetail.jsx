import { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import api from "../api/client";
import Topbar from "../components/Topbar";

export default function ResponseDetail() {
  const { id } = useParams();
  const [item, setItem] = useState(null);
  const [loading, setLoading] = useState(true);
  const [err, setErr] = useState("");

  useEffect(() => {
    (async () => {
      setErr("");
      setLoading(true);
      try {
        const { data } = await api.get(`/my/responses/${id}`);
        setItem(data);
      } catch (e) {
        console.error(e);
        setErr(e?.response?.data?.message || "No se pudo cargar el detalle.");
      } finally {
        setLoading(false);
      }
    })();
  }, [id]);

  return (
    <>
      <Topbar />
      <div className="container">
        <h1 className="h2 mb-16">Detalle</h1>
        {loading && <div className="card skeleton h96" />}
        {err && <div className="error">{err}</div>}
        {item && (
          <div className="card detail">
            <div className="row">
              <div className="muted">Resumen</div>
              <div className="strong">{item.summary || "—"}</div>
            </div>
            <div className="row">
              <div className="muted">Fecha</div>
              <div>{new Date(item.created_at).toLocaleString()}</div>
            </div>
            <div className="divider" />
            <div className="row">
              <div className="muted">Datos completos (JSON)</div>
              <pre className="json">{JSON.stringify(item, null, 2)}</pre>
            </div>
          </div>
        )}
      </div>
    </>
  );
}
