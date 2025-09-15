import { useEffect, useState } from "react";
import { useAuth } from "../context/AuthContext";
import api from "../api/client";
import Topbar from "../components/Topbar";
import { Link } from "react-router-dom";

export default function MyResponses() {
  const { token, logout } = useAuth();
  const [loading, setLoading] = useState(true);
  const [items, setItems] = useState([]);
  const [error, setError] = useState("");

  useEffect(() => {
    let mounted = true;
    async function fetchData() {
      try {
        setLoading(true);
        setError("");
        const { data } = await api.get("/my/responses");
        if (!mounted) return;
        if (Array.isArray(data)) {
          setItems(data);
        } else if (data && Array.isArray(data.items)) {
          setItems(data.items);
        } else if (data && Array.isArray(data.data)) {
          setItems(data.data);
        } else {
          setItems([]);
        }
      } catch (err) {
        if (!mounted) return;
        setError("No hay consultas para ver.");
      } finally {
        if (mounted) setLoading(false);
      }
    }
    if (token) {
      fetchData();
    } else {
      setLoading(false);
    }
    return () => { mounted = false; };
  }, [token]);

  return (
    <>
      <Topbar />
      <main className="container" style={{ paddingTop: 18, paddingBottom: 40 }}>
        <div style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}>
          <h1 className="h1" style={{ marginBottom: 18 }}>Mis Consultas</h1>
        </div>
        {loading && (
          <div className="stack-24">
            <div className="card skeleton h96" />
            <div className="card skeleton h96" />
            <div className="card skeleton h96" />
          </div>
        )}
        {!loading && error && (
          <div className="error">{error}</div>
        )}
        {!loading && !error && items.length === 0 && (
          <p className="muted">Aún no tienes consultas registradas.</p>
        )}
        {!loading && !error && items.length > 0 && (
          <ul className="list gap-lg">
            {items.map((it, index) => (
              <li key={it.id ?? `${it.payload_type}-${it.created_at}-${index}`}>
                <div className="card hoverable">
                  <div
                    className="list-item"
                    style={{ display: "flex", justifyContent: "space-between", alignItems: "center" }}
                  >
                    <div className="list-content">
                      <div className="title">{it.summary ?? it.title ?? "Consulta"}</div>
                      <div className="small muted">
                        {it.payload_type ?? it.type ?? "json_data"}
                        <span className="sep"> · </span>
                        {new Date(it.created_at ?? it.createdAt ?? Date.now()).toLocaleString()}
                      </div>
                    </div>
                    <Link
                    className="btn"
                    to={`/mis-consultas/${it.id}`}
                    >
                    Ver detalle
                    </Link>
                  </div>
                </div>
              </li>
            ))}
          </ul>
        )}
      </main>
    </>
  );
}
