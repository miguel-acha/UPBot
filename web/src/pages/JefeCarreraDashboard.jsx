import { useEffect, useMemo, useState } from "react";
import Topbar from "../components/Topbar";
import api from "../api/client";
import { useAuth } from "../context/AuthContext";

export default function JefeCarreraDashboard() {
  const { user } = useAuth();
  const role = useMemo(
    () => String(user?.role || user?.roles?.[0] || "").toLowerCase(),
    [user]
  );

  const [courses, setCourses] = useState([]);
  const [loading, setLoading] = useState(true);
  const [period, setPeriod] = useState("");
  const [error, setError] = useState("");

  useEffect(() => {
    if (role !== "head_of_program") return; // evita llamada si no corresponde
    fetchCourses();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [role]);

  async function fetchCourses() {
    setLoading(true);
    setError("");
    try {
      const response = await api.get("/courses");
      // Acepta array directo o {data: array}
      const arr = Array.isArray(response?.data)
        ? response.data
        : Array.isArray(response?.data?.data)
        ? response.data.data
        : [];
      setCourses(arr);
    } catch (err) {
      console.error("Error al cargar cursos", err);
      const msg =
        err?.response?.data?.message ||
        `No se pudieron cargar los cursos (${err?.response?.status || "ERR"}).`;
      setError(msg);
    } finally {
      setLoading(false);
    }
  }

  const filteredCourses = useMemo(() => {
    const list = Array.isArray(courses) ? courses : [];
    return period ? list.filter((c) => c?.period === period) : list;
  }, [courses, period]);

  // --- Autorización simple al nivel de componente ---
  if (role && role !== "head_of_program") {
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

        <div className="card login-card hoverable" style={{ textAlign: "left" }}>
          <h1 className="h2" style={{ textAlign: "center", marginBottom: 12 }}>
            Panel Jefe de Carrera
          </h1>
          <p className="small muted" style={{ textAlign: "center", marginTop: 0 }}>
            Visualiza cursos, inscripciones y genera documentos.
          </p>

          {/* Filtro */}
          <div className="form" style={{ marginTop: 20 }}>
            <label className="label" htmlFor="period">Filtrar por periodo</label>
            <div className="field">
              <span className="icon-left" aria-hidden>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M12 2l7 4v6c0 5-3.8 9.4-7 10-3.2-.6-7-5-7-10V6l7-4z"/>
                </svg>
              </span>
              <select id="period" value={period} onChange={(e) => setPeriod(e.target.value)}>
                <option value="">Todos</option>
                <option value="2024-1">2024-1</option>
                <option value="2024-2">2024-2</option>
                <option value="2025-1">2025-1</option>
              </select>
            </div>
          </div>

          {/* Lista cursos */}
          {loading ? (
            <div className="loading-list" style={{ marginTop: 16 }}>
              <div className="skel-card" />
              <div className="skel-card" />
            </div>
          ) : error ? (
            <p className="error" style={{ marginTop: 16 }}>{error}</p>
          ) : filteredCourses.length === 0 ? (
            <div className="note" style={{ marginTop: 16 }}>
              No hay cursos para mostrar con el filtro aplicado.
            </div>
          ) : (
            <ul className="list list-appear" style={{ marginTop: 16 }}>
              {filteredCourses.map((course) => (
                <li key={course?.id ?? `${course?.code}-${course?.period}`} className="list-item">
                  <div className="list-content">
                    <div className="title">{course?.name ?? "Curso"}</div>
                    <p className="small muted">
                      {(course?.code || "—")} — {(course?.period || "—")}
                    </p>
                  </div>
                  <span className="badge">
                    Inscritos: {course?.total_enrollments ?? 0}
                  </span>
                </li>
              ))}
            </ul>
          )}
        </div>
      </div>
    </>
  );
}
