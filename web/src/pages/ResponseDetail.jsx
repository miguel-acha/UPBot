import { useEffect, useMemo, useState } from "react";
import { useParams } from "react-router-dom";
import api from "../api/client";
import Topbar from "../components/Topbar";

/* ===== UI helpers ===== */
function Section({ title, right, children }) {
  return (
    <section className="section">
      <div className="section-head">
        <h2 className="h2">{title}</h2>
        {right}
      </div>
      <div className="section-body">{children}</div>
    </section>
  );
}

function KVPairs({ data }) {
  if (!data) return null;
  return (
    <div className="kv-grid">
      {Object.entries(data).map(([k, v]) => (
        <div key={k} className="kv">
          <div className="kv-k">{k}</div>
          <div className="kv-v">{v ?? "—"}</div>
        </div>
      ))}
    </div>
  );
}

/** Tabla de notas con columnas dinámicas */
function GradesTable({ courses, componentsHint = [] }) {
  const sorted = useMemo(() => {
    if (!Array.isArray(courses)) return [];
    return [...courses].sort((a, b) => (a.course_code || "").localeCompare(b.course_code || ""));
  }, [courses]);

  if (!sorted.length) return <p className="muted">No hay notas para mostrar.</p>;

  const discovered = useMemo(
    () => Array.from(new Set(sorted.flatMap(c => Object.keys(c.components || {})))),
    [sorted]
  );
  const cols = componentsHint.length ? componentsHint : discovered;

  const globalAvg = useMemo(() => {
    const nums = sorted.map(c => Number(c.average)).filter(Number.isFinite);
    return nums.length ? (nums.reduce((a, b) => a + b, 0) / nums.length).toFixed(2) : null;
  }, [sorted]);

  return (
    <>
      <div className="table">
        {/* OJO: thead + tr => aplica el grid también a la cabecera */}
        <div className="thead tr">
          <div className="th code">Código</div>
          <div className="th name">Materia</div>
          {cols.map(c => (
            <div key={c} className="th num">{c.replaceAll("_"," ")}</div>
          ))}
          <div className="th num">Prom.</div>
        </div>
        <div className="tbody">
          {sorted.map(c => (
            <div key={c.course_code} className="tr">
              <div className="td code">{c.course_code}</div>
              <div className="td name">{c.course_name}</div>
              {cols.map(col => (
                <div key={col} className="td num">{c.components?.[col] ?? "—"}</div>
              ))}
              <div className="td num">{c.average ?? "—"}</div>
            </div>
          ))}
        </div>
      </div>

      <div className="stat-grid" style={{ marginTop: 10 }}>
        <div className="stat">
          <div className="small muted">Promedio global</div>
          <div className="stat-num">{globalAvg ?? "—"}</div>
        </div>
        <div className="stat">
          <div className="small muted">Materias</div>
          <div className="stat-num">{sorted.length}</div>
        </div>
      </div>
      <p className="hint">Si algún componente no aparece, aún no fue cargado por la cátedra.</p>
    </>
  );
}

/* ===== Page ===== */
export default function ResponseDetail() {
  const { id } = useParams();
  const [meta, setMeta] = useState(null);
  const [enriched, setEnriched] = useState(null);
  const [loading, setLoading] = useState(true);
  const [err, setErr] = useState("");
  const [showRaw, setShowRaw] = useState(false);

  useEffect(() => {
    let alive = true;
    (async () => {
      setErr("");
      setLoading(true);
      try {
        const [a, b] = await Promise.all([
          api.get(`/my/responses/${id}`),
          api.get(`/my/responses/${id}/enriched`),
        ]);
        if (!alive) return;
        setMeta(a.data);
        setEnriched(b.data);
      } catch (e) {
        if (!alive) return;
        console.error(e);
        setErr(e?.response?.data?.message || "No se pudo cargar el detalle.");
      } finally {
        alive && setLoading(false);
      }
    })();
    return () => { alive = false; };
  }, [id]);

  return (
    <>
      <Topbar />
      <main className="container" style={{ paddingTop: 18, paddingBottom: 40 }}>
        <h1 className="h1 mb-12">Detalle de tu consulta</h1>

        {loading && <div className="card skeleton h96" />}
        {err && <div className="error">{err}</div>}

        {!loading && !err && (
          <article className="card detail-card">
            <header className="detail-head">
              <div className="detail-title">{meta?.summary || "Consulta"}</div>
              <div className="detail-sub">{new Date(meta?.created_at ?? Date.now()).toLocaleString()}</div>
            </header>

            {/* CONSTANCIA */}
            {enriched?.kind === "constancia" && (
              <Section title="Constancia de inscripción" right={<span className="badge">Semestre {enriched?.semester ?? "—"}</span>}>
                <KVPairs data={enriched.fields} />
                {enriched?.message && <div className="note">{enriched.message}</div>}
              </Section>
            )}

            {/* NOTAS */}
            {enriched?.kind === "grades" && (
              <Section title="Notas del semestre" right={<span className="badge">Semestre ID {enriched.semester_id}</span>}>
                <GradesTable courses={enriched.courses} componentsHint={enriched.components_hint} />
              </Section>
            )}

            {/* INSCRIPCIONES (si lo usas) */}
            {enriched?.kind === "enrollments" && (
              <Section title="Materias inscritas" right={<span className="badge">Semestre ID {enriched.semester_id}</span>}>
                {/* Puedes reutilizar tu EnrollmentList aquí si lo necesitas */}
              </Section>
            )}

            {/* GENÉRICO / TÉCNICO */}
            {(!enriched || enriched?.kind === "generic") && (
              <>
                <Section title="Resumen">
                  <div className="kv-grid">
                    <div className="kv"><div className="kv-k">Tipo</div><div className="kv-v">{meta?.type || meta?.payload_type || "—"}</div></div>
                    <div className="kv"><div className="kv-k">Documento</div><div className="kv-v">{meta?.document_id ?? "—"}</div></div>
                  </div>
                </Section>
                {enriched?.raw && (
                  <Section title="Información técnica" right={
                    <button className="link-btn" onClick={() => setShowRaw(v => !v)}>
                      {showRaw ? "Ocultar JSON" : "Ver JSON"}
                    </button>
                  }>
                    {showRaw ? <pre className="json">{JSON.stringify(enriched.raw, null, 2)}</pre> : <p className="hint">Datos crudos disponibles.</p>}
                  </Section>
                )}
              </>
            )}
          </article>
        )}
      </main>
    </>
  );
}
