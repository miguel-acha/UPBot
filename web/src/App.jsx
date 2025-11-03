import { Routes, Route, Navigate } from "react-router-dom";
import ProtectedRoute from "./components/ProtectedRoute";
import Login from "./pages/Login";
import MyResponses from "./pages/MyResponses";
import ResponseDetail from "./pages/ResponseDetail";
import AdminDashboard from "./pages/AdminDashboard";
import ProfesorDashboard from "./pages/ProfesorDashboard";
import JefeCarreraDashboard from "./pages/JefeCarreraDashboard";

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<Navigate to="/login" replace />} />
      <Route path="/login" element={<Login />} />

      {/* Student / general */}
      <Route element={<ProtectedRoute />}>
        <Route path="/mis-consultas" element={<MyResponses />} />
        <Route path="/mis-consultas/:id" element={<ResponseDetail />} />
      </Route>

      {/* Admin only */}
      <Route element={<ProtectedRoute roles={["admin"]} />}>
        <Route path="/admin" element={<AdminDashboard />} />
      </Route>

      {/* Docente only */}
      <Route element={<ProtectedRoute roles={["teacher"]} />}>
        <Route path="/profesor" element={<ProfesorDashboard />} />
      </Route>

      {/* Jefe de carrera only */}
      <Route element={<ProtectedRoute roles={["head_of_program"]} />}>
        <Route path="/jefe-carrera" element={<JefeCarreraDashboard />} />
      </Route>

      <Route path="*" element={<Navigate to="/login" replace />} />
    </Routes>
  );
}
