import { useEffect, useMemo, useState } from "react";
import Topbar from "../components/Topbar";
import api from "../api/client";
import { useAuth } from "../context/AuthContext";
import {
  getSemesters,
  getKpis,
  pdfOfferings,
  pdfEnrolls,
  pdfGrades,
  downloadBlob,
} from "../api/reports";

export default function JefeCarreraDashboard() {
  const { user } = useAuth();
  const role = useMemo(
    () => String(user?.role || (user?.roles && user?.roles[0]) || "").toLowerCase(),
    [user]
  );
  const isHead = role === "head_of_program";

  const [periods, setPeriods] = useState([]);
  const [period, setPeriod] = useState("");
  const [q, setQ] = useState("");

  const [courses, setCourses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");

  const [kpi, setKpi] = useState(null);

  useEffect(() => {
    if (!isHead) return;
    loadSemesters();
  }, [isHead]);

  useEffect(() => {
    if (!isHead) return;
    fetchCourses();
    fetchKpis();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [period, q, isHead]);

  async function loadSemesters() {
    try {
      const { data } = await getSemesters();
      setPeriods(Array.isArray(data) ? data : []);
    } catch {
      setPeriods([]);
    }
  }

  async function fetchKpis() {
    try {
      const { data } = await getKpis({ period: period || undefined });
      setKpi(data);
    } catch {
      setKpi(null);
    }
  }

  async function fetchCourses() {
    setLoading(true);
    setError("");
    try {
      const params = {};
      if (period) params.period = period;
      if (q) params.q = q;
      const res = await api.get("/courses", { params });

      const raw = Array.isArray(res?.data)
        ? res.data
        : Array.isArray(res?.data?.data)
        ? res.data.data
        : [];

      // 🔧 Normaliza nombres y asegura el conteo de inscritos
      const arr = raw.map((it) => ({
        ...it,
        offering_id: it.offering_id ?? it.id ?? it.offeringId,
        total_enrollments:
          it.total_enrollments ??
          it.enrollments_count ??
          it.enrollments ??
          it.total ??
          it.inscritos ??
          (it.metrics && (it.metrics.total_enrollments ?? it.metrics.enrollments)) ??
          0,
      }));

      setCourses(arr);
    } catch (err) {
      setError(err?.response?.data?.message || "No se pudieron cargar las ofertas.");
      setCourses([]);
    } finally {
      setLoading(false);
    }
  }

  const onDownload = async (what) => {
    try {
      const params = period ? { period } : {};
      let r, name;
      if (what === "offerings") {
        r = await pdfOfferings(params);
        name = `ofertas${period ? "-" + period : ""}.pdf`;
      }
      if (what === "enrolls") {
        r = await pdfEnrolls(params);
        name = `inscritos${period ? "-" + period : ""}.pdf`;
      }
      if (what === "grades") {
        r = await pdfGrades(params);
        name = `notas${period ? "-" + period : ""}.pdf`;
      }
      if (r?.data) downloadBlob(r.data, name);
    } catch {
      alert("No se pudo descargar el PDF");
    }
  };

  if (role && !isHead) {
    return (
      <>
        <Topbar />
        <div className="screen">
          <div className="bg-blob" />
          <div className="bg-blob b2" />
          <div className="card login-card hoverable" style={{ textAlign: "center" }}>
            <h1 className="h2">No autorizado</h1>
            <p className="small muted">Esta sección es solo para Jefes de carrera.</p>
          </div>
        </div>
      </>
    );
  }

  return (
    <>
      <Topbar />
      <div className="screen">
        <div className="bg-blob" />
        <div className="bg-blob b2" />
        <div className="card login-card hoverable" style={{ textAlign: "left", width: "min(900px,95vw)" }}>
          <h1 className="h2" style={{ textAlign: "center", marginBottom: 12 }}>
            Panel Jefe de Carrera
          </h1>
          <p className="small muted" style={{ textAlign: "center", marginTop: 0 }} />

          {/* Filtros */}
          <div className="form" style={{ marginTop: 14 }}>
            <div className="field">
              <span className="icon-left" aria-hidden>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2l7 4v6c0 5-3.8 9.4-7 10-3.2-.6-7-5-7-10V6l7-4z" />
                </svg>
              </span>
              <select id="period" value={period} onChange={(e) => setPeriod(e.target.value)}>
                <option value="">Todos los periodos</option>
                {periods.map((p) => (
                  <option key={p} value={p}>{p}</option>
                ))}
              </select>
            </div>

            <div className="field">
              <span className="icon-left" aria-hidden>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M21 21l-4.3-4.3M10 18a8 8 0 110-16 8 8 0 010 16z" />
                </svg>
              </span>
              <input
                placeholder="Buscar curso (código o nombre)"
                value={q}
                onChange={(e) => setQ(e.target.value)}
              />
            </div>
          </div>

          {/* KPIs */}
          <div className="stat-grid" style={{ marginTop: 12 }}>
            <div className="stat">
              <div className="small muted">Ofertas</div>
              <div className="stat-num">{kpi?.offerings ?? "—"}</div>
            </div>
            <div className="stat">
              <div className="small muted">Inscritos</div>
              <div className="stat-num">{kpi?.enrollments ?? "—"}</div>
            </div>
            <div className="stat">
              <div className="small muted">Promedio notas</div>
              <div className="stat-num">{kpi?.avg_score ?? "—"}</div>
            </div>
            <div className="stat">
              <div className="small muted">Periodo</div>
              <div className="stat-num">{kpi?.period || "Todos"}</div>
            </div>
          </div>

          {/* Exportaciones */}
          <div className="section">
            <div className="section-head">
              <h3 className="section-title">Exportaciones (PDF)</h3>
              <div style={{ display: "flex", gap: 8 }}>
                <button className="link-btn" onClick={() => onDownload("offerings")}>Ofertas PDF</button>
                <button className="link-btn" onClick={() => onDownload("enrolls")}>Inscritos PDF</button>
                <button className="link-btn" onClick={() => onDownload("grades")}>Notas PDF</button>
              </div>
            </div>
          </div>

          {/* Lista ofertas */}
          {loading ? (
            <div className="loading-list" style={{ marginTop: 16 }}>
              <div className="skel-card" /><div className="skel-card" />
            </div>
          ) : error ? (
            <p className="error" style={{ marginTop: 16 }}>{error}</p>
          ) : (courses ?? []).length === 0 ? (
            <div className="note" style={{ marginTop: 16 }}>No hay resultados con el filtro.</div>
          ) : (
            <ul className="list list-appear" style={{ marginTop: 16 }}>
              {courses.map((c) => (
                <li key={c.offering_id} className="list-item">
                  <div className="list-content">
                    <div className="title">{c.name}</div>
                    <p className="small muted">
                      {c.code} — {c.period}{c.group ? ` — Grupo ${c.group}` : ""}
                    </p>
                  </div>
                  <span className="badge">Inscritos: {c.total_enrollments}</span>
                </li>
              ))}
            </ul>
          )}
        </div>
      </div>
    </>
  );
}
