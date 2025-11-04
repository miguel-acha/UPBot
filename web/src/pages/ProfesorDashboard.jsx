import { useEffect, useMemo, useState } from "react";
import Topbar from "../components/Topbar";
import api from "../api/client";
import { useAuth } from "../context/AuthContext";
import { getSemesters, pdfEnrolls, pdfGrades, downloadBlob } from "../api/reports";

export default function ProfesorDashboard() {
  const { user } = useAuth();
  const role = useMemo(
    () => String(user?.role || (user?.roles && user?.roles[0]) || "").toLowerCase(),
    [user]
  );

  if (role && role !== "teacher") {
    return (
      <>
        <Topbar />
        <div className="screen">
          <div className="bg-blob" />
          <div className="bg-blob b2" />
          <div className="card login-card hoverable" style={{ textAlign: "center" }}>
            <h1 className="h2">No autorizado</h1>
            <p className="small muted">Solo para Docentes.</p>
          </div>
        </div>
      </>
    );
  }

  // ---------- STATE ----------
  const [periods, setPeriods] = useState([]);
  const [periodFilter, setPeriodFilter] = useState("");
  const [q, setQ] = useState("");

  const [courses, setCourses] = useState([]);
  const [loadingCourses, setLoadingCourses] = useState(true);
  const [coursesError, setCoursesError] = useState("");

  const [selectedOfferingId, setSelectedOfferingId] = useState(null);
  const [enrollments, setEnrollments] = useState([]);
  const [loadingEnroll, setLoadingEnroll] = useState(false);
  const [enrollError, setEnrollError] = useState("");

  // ---------- HELPERS SOLO-UI ----------
  const ORDER = ["final", "primer_parcial", "segundo_parcial"];
  const normalizeComponent = (name) => {
    const c = String(name || "").toLowerCase();
    if (c === "parcial1" || c === "primer_parcial") return "primer_parcial";
    if (c === "parcial2" || c === "segundo_parcial") return "segundo_parcial";
    if (c === "final") return "final";
    return c;
  };
  const sortGrades = (grades = []) => {
    const g = (grades ?? []).map(x => ({ ...x, component: normalizeComponent(x.component) }));
    g.sort((a, b) => {
      const ia = ORDER.indexOf(a.component);
      const ib = ORDER.indexOf(b.component);
      return (ia < 0 ? 999 : ia) - (ib < 0 ? 999 : ib);
    });
    return g;
  };
  const finalScore = (grades = []) => {
    const f = (grades ?? []).find(x => normalizeComponent(x.component) === "final");
    return typeof f?.score === "number" ? f.score : -1;
  };

  // Lista “presentacional” para la UI (no cambia los datos originales)
  const uiEnrollments = useMemo(() => {
    const withSortedGrades = (enrollments ?? []).map(e => ({
      ...e,
      grades: sortGrades(e.grades || []),
      _finalScore: finalScore(e.grades || [])
    }));
    // Orden visual por nota final desc, luego por nombre
    withSortedGrades.sort((a, b) => {
      const d = (b._finalScore ?? -1) - (a._finalScore ?? -1);
      if (d !== 0) return d;
      return String(a.student_name || "").localeCompare(String(b.student_name || ""));
    });
    return withSortedGrades;
  }, [enrollments]);

  // ---------- EFFECTS ----------
  useEffect(() => { loadSemesters(); }, []);
  useEffect(() => { fetchCourses(); /* eslint-disable-next-line */ }, [periodFilter, q]);

  async function loadSemesters() {
    try {
      const { data } = await getSemesters();
      setPeriods(Array.isArray(data) ? data : []);
    } catch {
      setPeriods([]);
    }
  }

  async function fetchCourses() {
    setLoadingCourses(true);
    setCoursesError("");
    try {
      const params = {};
      if (periodFilter) params.period = periodFilter;
      if (q) params.q = q;
      const res = await api.get("/courses", { params });
      const arr = Array.isArray(res?.data)
        ? res.data
        : Array.isArray(res?.data?.data)
        ? res.data.data
        : [];
      setCourses(arr);
    } catch (err) {
      setCoursesError(err?.response?.data?.message || "No se pudieron obtener los cursos.");
      setCourses([]);
    } finally {
      setLoadingCourses(false);
    }
  }

  // Carga inscritos + notas (solo lectura)
  async function fetchEnrollments(offeringId) {
    setSelectedOfferingId(offeringId);
    setEnrollError("");
    setLoadingEnroll(true);
    try {
      const res = await api.get("/manage/enrollments", { params: { course_offering_id: offeringId } });
      const base = Array.isArray(res?.data) ? res.data : [];
      const withGrades = await Promise.all(
        base.map(async (e) => {
          try {
            const g = await api.get("/manage/grades", { params: { enrollment_id: e.id } });
            return { ...e, grades: Array.isArray(g?.data) ? g.data : [] };
          } catch {
            return { ...e, grades: [] };
          }
        })
      );
      setEnrollments(withGrades);
    } catch (err) {
      const msg = err?.response?.data?.message || "No se pudieron obtener los inscritos.";
      const detail = err?.response?.data?.detail || err?.message;
      console.error("ENROLLMENTS ERROR:", err?.response?.status, detail);
      setEnrollError(`${msg}${detail ? " — " + detail : ""}`);
      setEnrollments([]);
    } finally {
      setLoadingEnroll(false);
    }
  }

  // ---------- DESCARGAS ----------
  const downloadEnrolls = async () => {
    try {
      const params = {};
      if (selectedOfferingId) params.course_offering_id = selectedOfferingId;
      if (periodFilter) params.period = periodFilter;
      const r = await pdfEnrolls(params);
      downloadBlob(
        r.data,
        `inscritos${selectedOfferingId ? "-off" + selectedOfferingId : ""}${periodFilter ? "-" + periodFilter : ""}.pdf`
      );
    } catch {
      alert("No se pudo descargar inscritos");
    }
  };

  const downloadGrades = async () => {
    try {
      const params = {};
      if (selectedOfferingId) params.course_offering_id = selectedOfferingId;
      if (periodFilter) params.period = periodFilter;
      const r = await pdfGrades(params);
      downloadBlob(
        r.data,
        `notas${selectedOfferingId ? "-off" + selectedOfferingId : ""}${periodFilter ? "-" + periodFilter : ""}.pdf`
      );
    } catch {
      alert("No se pudo descargar notas");
    }
  };

  // ---------- RENDER ----------
  return (
    <>
      <Topbar />
      <div className="screen">
        <div className="bg-blob" />
        <div className="bg-blob b2" />

        <div className="card login-card hoverable" style={{ textAlign: "left", width: "min(1100px,96vw)" }}>
          <h1 className="h2" style={{ textAlign: "center", marginBottom: 12 }}>Panel Profesor</h1>

          {/* Filtros */}
          <div className="form" style={{ marginTop: 12 }}>
            <div className="field">
              <span className="icon-left" aria-hidden>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l7 4v6c0 5-3.8 9.4-7 10-3.2-.6-7-5-7-10V6l7-4z"/></svg>
              </span>
              <select id="periodFilter" value={periodFilter} onChange={(e) => setPeriodFilter(e.target.value)}>
                <option value="">Todos los periodos</option>
                {periods.map((p) => (<option key={p} value={p}>{p}</option>))}
              </select>
            </div>

            <div className="field">
              <span className="icon-left" aria-hidden>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M21 21l-4.3-4.3M10 18a8 8 0 110-16 8 8 0 010 16z"/></svg>
              </span>
              <input
                placeholder="Buscar curso (código o nombre)"
                value={q}
                onChange={(e) => setQ(e.target.value)}
              />
            </div>
          </div>

          {/* Cursos */}
          {loadingCourses ? (
            <div className="loading-list" style={{ marginTop: 16 }}>
              <div className="skel-card" /><div className="skel-card" />
            </div>
          ) : coursesError ? (
            <p className="error" style={{ marginTop: 16 }}>{coursesError}</p>
          ) : (courses ?? []).length === 0 ? (
            <div className="note" style={{ marginTop: 16 }}>No hay cursos con el filtro.</div>
          ) : (
            <ul className="list list-appear" style={{ marginTop: 16 }}>
              {(courses ?? []).map((course) => (
                <li key={course.offering_id} className="list-item">
                  <div className="list-content">
                    <div className="title">{course.name}</div>
                    <p className="small muted">
                      {course.code} — {course.period ?? "—"}{course.group ? ` — Grupo ${course.group}` : ""}
                    </p>
                  </div>
                  <button className="link-btn" onClick={() => fetchEnrollments(course.offering_id)}>
                    Ver inscritos
                  </button>
                </li>
              ))}
            </ul>
          )}

          {/* Inscritos + Notas (solo lectura, UI ordenada) */}
          {selectedOfferingId != null && (
            <div className="section" style={{ margin: "20px auto 0", maxWidth: 1000 }}>
              <div className="section-head" style={{ alignItems: "center" }}>
                <h3 className="section-title" style={{ marginRight: "auto" }}>Inscritos</h3>
                <div style={{ display: "flex", gap: 8 }}>
                  <button className="link-btn" onClick={downloadEnrolls}>Inscritos PDF</button>
                  <button className="link-btn" onClick={downloadGrades}>Notas PDF</button>
                </div>
              </div>

              {loadingEnroll ? (
                <div className="loading-list"><div className="skel-card h96" /></div>
              ) : enrollError ? (
                <p className="error">{enrollError}</p>
              ) : (uiEnrollments ?? []).length === 0 ? (
                <div className="note">No hay inscritos en esta oferta.</div>
              ) : (
                <ul
                  className="list"
                  style={{
                    display: "grid",
                    gridTemplateColumns: "repeat(auto-fill, minmax(280px, 1fr))",
                    gap: 16,
                    alignItems: "stretch",
                    marginTop: 8
                  }}
                >
                  {uiEnrollments.map((enr) => {
                    const grades = enr.grades || [];
                    const f = grades.find(g => normalizeComponent(g.component) === "final");
                    const rest = grades.filter(g => normalizeComponent(g.component) !== "final");
                    return (
                      <li
                        key={enr.id}
                        className="list-item"
                        style={{
                          alignItems: "flex-start",
                          minHeight: 180,
                          padding: 16,
                          display: "flex",
                          flexDirection: "column",
                          borderRadius: 12
                        }}
                      >
                        <div className="list-content" style={{ width: "100%" }}>
                          <strong>{enr.student_name ?? "Estudiante"}</strong>
                          <p className="small muted">CI: {enr.student_ci ?? "—"}</p>

                          {/* FINAL destacado */}
                          <div style={{ marginTop: 8, marginBottom: 6 }}>
                            <span className="badge" style={{ marginRight: 8, opacity: 0.85 }}>
                              final
                            </span>
                            <span
                              className="pill"
                              style={{
                                minWidth: 96,
                                display: "inline-flex",
                                justifyContent: "center",
                                fontWeight: 600,
                                fontSize: 14,
                                padding: "6px 10px"
                              }}
                            >
                              {typeof f?.score === "number" ? f.score : "—"}
                            </span>
                          </div>

                          {/* Restantes en orden fijo */}
                          <div className="small muted" style={{ marginTop: 2, marginBottom: 6 }}>Notas:</div>
                          {(rest ?? []).length === 0 ? (
                            <div className="pill" style={{ minWidth: 120, textAlign: "center" }}>Sin notas</div>
                          ) : (
                            <ul className="list" style={{ display: "flex", flexDirection: "column", gap: 6 }}>
                              {rest.map((g) => (
                                <li key={g.id} className="list-item" style={{ padding: 0 }}>
                                  <div className="list-content">
                                    <span className="badge">{normalizeComponent(g.component)}</span>
                                    <span
                                      className="pill"
                                      style={{
                                        marginLeft: 8,
                                        minWidth: 96,
                                        display: "inline-flex",
                                        justifyContent: "center"
                                      }}
                                    >
                                      {g.score ?? "—"}
                                    </span>
                                  </div>
                                </li>
                              ))}
                            </ul>
                          )}
                        </div>
                      </li>
                    );
                  })}
                </ul>
              )}
            </div>
          )}
        </div>
      </div>
    </>
  );
}
