import { useEffect, useState } from "react";
import { useAuth } from "../context/AuthContext";
import api from "../api/client";
import Topbar from "../components/Topbar";
import { Link } from "react-router-dom";

/** Normaliza lo que venga del backend a un array de items */
function extractItems(payload) {
  if (!payload) return [];
  if (Array.isArray(payload)) return payload;
  if (Array.isArray(payload.data)) return payload.data;
  if (Array.isArray(payload.items)) return payload.items;
  if (payload.data && Array.isArray(payload.data.data)) return payload.data.data;
  if (payload.items && Array.isArray(payload.items.data)) return payload.items.data;
  return [];
}

export default function MyResponses() {
  const { token, user } = useAuth();
  const [loading, setLoading] = useState(true);
  const [items, setItems] = useState([]);
  const [error, setError] = useState("");
  const [debugMsg, setDebugMsg] = useState("");

  useEffect(() => {
    let mounted = true;

    async function fetchData() {
      try {
        setLoading(true);
        setError("");
        setDebugMsg("");

        const res = await api.get("/my/responses");
        console.log("[/my/responses] raw:", res.data);

        if (!mounted) return;

        const arr = extractItems(res.data);

        if (!Array.isArray(arr)) {
          setItems([]);
          setDebugMsg("La API devolvió un formato inesperado. Revisa la consola del navegador.");
        } else {
          setItems(arr);
          if (arr.length === 0) {
            const hasPaginationMeta =
              res?.data &&
              (typeof res.data.current_page !== "undefined" ||
                res?.data?.meta ||
                res?.data?.links);
            if (hasPaginationMeta) {
              setDebugMsg("No hay resultados en esta página del paginador.");
            }
          }
        }
      } catch (err) {
        if (!mounted) return;
        console.error("[/my/responses] error:", err?.response?.status, err?.response?.data || err);
        const serverMsg =
          err?.response?.data?.message ||
          err?.message ||
          "No hay consultas para ver.";
        setError(serverMsg);
      } finally {
        if (mounted) setLoading(false);
      }
    }

    if (token && user?.role === "student") {
      fetchData();
    } else {
      setLoading(false);
      setDebugMsg("Este panel solo está disponible para estudiantes.");
    }

    return () => {
      mounted = false;
    };
  }, [token, user]);

  return (
    <>
      <Topbar />
      <main className="container" style={{ paddingTop: 18, paddingBottom: 40 }}>
        <div
          style={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
          }}
        >
          <h1 className="h1" style={{ marginBottom: 18 }}>
            Mis Consultas
          </h1>
        </div>

        {loading && (
          <div className="loading-list">
            <div className="skel-card" />
            <div className="skel-card" />
            <div className="skel-card" />
          </div>
        )}

        {!loading && error && <div className="error">{error}</div>}

        {!loading && !error && debugMsg && (
          <p className="small muted" style={{ marginTop: 8 }}>{debugMsg}</p>
        )}

        {!loading && !error && items.length === 0 && !debugMsg && (
          <p className="muted">Aún no tienes consultas registradas.</p>
        )}

        {!loading && !error && items.length > 0 && (
          <ul className="list list-appear">
            {items.map((it, index) => {
              const id = it.id ?? it.payload_id ?? it.interaction_id ?? index;
              const title = it.summary ?? it.title ?? "Consulta";
              const type = it.payload_type ?? it.type ?? "json_data";
              const created =
                it.created_at ?? it.createdAt ?? it.created ?? Date.now();

              return (
                <li key={id} className="query-card">
                  <div className="list-item">
                    <div className="list-content">
                      <div className="title">{title}</div>
                      <div className="small muted">
                        {type}
                        <span className="sep"> · </span>
                        {new Date(created).toLocaleString()}
                      </div>
                    </div>
                    <Link className="link-btn" to={`/mis-consultas/${id}`}>
                      Ver detalle
                    </Link>
                  </div>
                </li>
              );
            })}
          </ul>
        )}
      </main>
    </>
  );
}