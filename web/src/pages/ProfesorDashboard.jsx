import { useEffect, useMemo, useState } from "react";
import Topbar from "../components/Topbar";
import api from "../api/client";
import { useAuth } from "../context/AuthContext";

export default function ProfesorDashboard() {
  const { user } = useAuth();
  const role = useMemo(() => String(user?.role || "").toLowerCase(), [user]);

  const [periodFilter, setPeriodFilter] = useState("");
  const [courses, setCourses] = useState([]);
  const [loadingCourses, setLoadingCourses] = useState(true);
  const [coursesError, setCoursesError] = useState("");

  const [selectedOfferingId, setSelectedOfferingId] = useState(null);
  const [enrollments, setEnrollments] = useState([]);
  const [loadingEnroll, setLoadingEnroll] = useState(false);
  const [enrollError, setEnrollError] = useState("");

  // Para editar estado y notas
  const [gradeEditors, setGradeEditors] = useState({}); // enrollmentId -> {component, score}

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

  useEffect(() => {
    fetchCourses();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [periodFilter]);

  async function fetchCourses() {
    setLoadingCourses(true);
    setCoursesError("");
    try {
      // Usamos tu endpoint existente de cursos (que ya usabas en la versión previa)
      const res = await api.get("/courses", { params: periodFilter ? { period: periodFilter } : {} });
      const arr = Array.isArray(res?.data) ? res.data : Array.isArray(res?.data?.data) ? res.data.data : [];
      setCourses(arr);
    } catch (err) {
      setCoursesError(err?.response?.data?.message || "No se pudieron obtener los cursos.");
      setCourses([]);
    } finally {
      setLoadingCourses(false);
    }
  }

  async function fetchEnrollments(offeringId) {
    setSelectedOfferingId(offeringId);
    setEnrollError("");
    setLoadingEnroll(true);
    try {
      const res = await api.get("/manage/enrollments", {
        params: { course_offering_id: offeringId }, // permitido teacher
      });
      const arr = Array.isArray(res?.data) ? res.data : [];
      setEnrollments(arr);
    } catch (err) {
      setEnrollError(err?.response?.data?.message || "No se pudieron obtener los inscritos.");
      setEnrollments([]);
    } finally {
      setLoadingEnroll(false);
    }
  }

  async function updateEnrollmentStatus(enrollmentId, status) {
    try {
      await api.put(`/manage/enrollments/${enrollmentId}`, { status }); // permitido teacher
      setEnrollments((old) => old.map((e) => (e.id === enrollmentId ? { ...e, status } : e)));
    } catch (err) {
      alert(err?.response?.data?.message || "No se pudo actualizar el estado");
    }
  }

  async function loadGrades(enrollmentId) {
    try {
      const res = await api.get("/manage/grades", { params: { enrollment_id: enrollmentId } });
      const list = Array.isArray(res?.data) ? res.data : [];
      setEnrollments((old) =>
        old.map((e) => (e.id === enrollmentId ? { ...e, grades: list } : e))
      );
    } catch (err) {
      alert("No se pudieron cargar las notas");
    }
  }

  async function upsertGrade(enrollmentId) {
    const editor = gradeEditors[enrollmentId] || {};
    if (!editor.component) return alert("Completa el componente de la nota");
    try {
      await api.post("/manage/grades/upsert", {
        enrollment_id: enrollmentId,
        component: editor.component,
        score: editor.score === "" ? null : Number(editor.score),
      });
      setGradeEditors((g) => ({ ...g, [enrollmentId]: { component: "", score: "" } }));
      await loadGrades(enrollmentId);
    } catch (err) {
      alert(err?.response?.data?.message || "No se pudo guardar la nota");
    }
  }

  async function deleteGrade(gradeId, enrollmentId) {
    if (!confirm("¿Eliminar nota?")) return;
    try {
      await api.delete(`/manage/grades/${gradeId}`);
      await loadGrades(enrollmentId);
    } catch {
      alert("No se pudo eliminar la nota");
    }
  }

  return (
    <>
      <Topbar />
      <div className="screen">
        <div className="bg-blob" />
        <div className="bg-blob b2" />

        <div className="card login-card hoverable" style={{ textAlign: "left" }}>
          <h1 className="h2" style={{ textAlign: "center", marginBottom: 12 }}>
            Panel Profesor
          </h1>
          <p className="small muted" style={{ textAlign: "center", marginTop: 0 }}>
            Gestión de cursos, inscritos y notas.
          </p>

          {/* Filtro periodo */}
          <div className="form" style={{ marginTop: 12 }}>
            <label className="label" htmlFor="periodFilter">Filtrar por periodo (opcional)</label>
            <div className="field">
              <span className="icon-left" aria-hidden>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l7 4v6c0 5-3.8 9.4-7 10-3.2-.6-7-5-7-10V6l7-4z"/></svg>
              </span>
              <select
                id="periodFilter"
                value={periodFilter}
                onChange={(e) => setPeriodFilter(e.target.value)}
              >
                <option value="">Todos</option>
                <option value="2024-1">2024-1</option>
                <option value="2024-2">2024-2</option>
                <option value="2025-1">2025-1</option>
              </select>
            </div>
          </div>

          {/* Cursos */}
          {loadingCourses ? (
            <div className="loading-list" style={{ marginTop: 16 }}>
              <div className="skel-card" />
              <div className="skel-card" />
            </div>
          ) : coursesError ? (
            <p className="error" style={{ marginTop: 16 }}>{coursesError}</p>
          ) : courses.length === 0 ? (
            <div className="note" style={{ marginTop: 16 }}>
              No hay cursos para mostrar con el filtro seleccionado.
            </div>
          ) : (
            <ul className="list list-appear" style={{ marginTop: 16 }}>
              {courses.map((course) => (
                <li key={course.id} className="list-item">
                  <div className="list-content">
                    <div className="title">{course.name}</div>
                    <p className="small muted">
                      {course.code} — {course.period ?? course.semester_id ?? "—"}
                    </p>
                  </div>
                  <button className="link-btn" onClick={() => fetchEnrollments(course.id)}>
                    Ver inscritos
                  </button>
                </li>
              ))}
            </ul>
          )}

          {/* Inscritos + Notas */}
          {selectedOfferingId != null && (
            <div className="section" style={{ marginTop: 20 }}>
              <div className="section-head">
                <h3 className="section-title">Inscritos</h3>
              </div>

              {loadingEnroll ? (
                <div className="loading-list"><div className="skel-card h96" /></div>
              ) : enrollError ? (
                <p className="error">{enrollError}</p>
              ) : enrollments.length === 0 ? (
                <div className="note">No hay inscritos en esta oferta.</div>
              ) : (
                <ul className="list">
                  {enrollments.map((enr) => (
                    <li key={enr.id} className="list-item">
                      <div className="list-content">
                        <strong>{enr.student_name ?? enr.student?.full_name ?? "Estudiante"}</strong>
                        <p className="small muted">CI: {enr.student_ci ?? enr.student?.ci ?? "—"}</p>

                        {/* Notas */}
                        <div className="section" style={{ marginTop: 6 }}>
                          <div className="small muted" style={{ marginBottom: 6 }}>
                            Notas:
                            <button
                              className="link-btn"
                              style={{ marginLeft: 8 }}
                              onClick={() => loadGrades(enr.id)}
                            >
                              Actualizar
                            </button>
                          </div>

                          {(enr.grades ?? []).length === 0 ? (
                            <div className="pill">Sin notas</div>
                          ) : (
                            <ul className="list" style={{ gap: 8 }}>
                              {enr.grades.map((g) => (
                                <li key={g.id} className="list-item">
                                  <div className="list-content">
                                    <span className="badge">{g.component}</span>
                                    <span className="pill" style={{ marginLeft: 8 }}>
                                      {g.score ?? "—"}
                                    </span>
                                  </div>
                                  <button className="link-btn" onClick={() => deleteGrade(g.id, enr.id)}>
                                    Eliminar
                                  </button>
                                </li>
                              ))}
                            </ul>
                          )}

                          {/* Editor de nota */}
                          <div className="kv-grid" style={{ marginTop: 8 }}>
                            <div className="field">
                              <span className="icon-left" aria-hidden>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M4 6h16v12H4z"/></svg>
                              </span>
                              <input
                                type="text"
                                placeholder="Componente (p. ej. parcial1)"
                                value={gradeEditors[enr.id]?.component || ""}
                                onChange={(e)=>setGradeEditors((g)=>({ ...g, [enr.id]: { ...(g[enr.id]||{}), component: e.target.value }}))}
                              />
                            </div>
                            <div className="field">
                              <span className="icon-left" aria-hidden>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M12 7v10M7 12h10"/></svg>
                              </span>
                              <input
                                type="number"
                                placeholder="Nota (0-100)"
                                value={gradeEditors[enr.id]?.score ?? ""}
                                onChange={(e)=>setGradeEditors((g)=>({ ...g, [enr.id]: { ...(g[enr.id]||{}), score: e.target.value }}))}
                              />
                            </div>
                          </div>
                          <button className="btn btn-primary" style={{ marginTop: 8 }} onClick={() => upsertGrade(enr.id)}>
                            Guardar nota
                          </button>
                        </div>
                      </div>

                      {/* Estado inscripción */}
                      <div style={{ minWidth: 220 }}>
                        <label className="label">Estado</label>
                        <div className="field">
                          <span className="icon-left" aria-hidden>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M7 10l5 5 5-5H7z"/></svg>
                          </span>
                          <select
                            value={enr.status}
                            onChange={(e)=>updateEnrollmentStatus(enr.id, e.target.value)}
                          >
                            <option value="enrolled">Inscrito</option>
                            <option value="dropped">Retirado</option>
                            <option value="approved">Aprobado</option>
                            <option value="failed">Reprobado</option>
                          </select>
                        </div>
                      </div>
                    </li>
                  ))}
                </ul>
              )}
            </div>
          )}
        </div>
      </div>
    </>
  );
}
