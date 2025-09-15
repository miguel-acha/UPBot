import { Routes, Route, Navigate } from "react-router-dom";
import ProtectedRoute from "./components/ProtectedRoute";
import Login from "./pages/Login";
import MyResponses from "./pages/MyResponses";
import ResponseDetail from "./pages/ResponseDetail";
import AdminDashboard from "./pages/AdminDashboard"; // Agrega el import

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<Navigate to="/login" replace />} />
      <Route path="/login" element={<Login />} />

      {/* Rutas protegidas para usuarios autenticados */}
      <Route element={<ProtectedRoute />}>
        <Route path="/mis-consultas" element={<MyResponses />} />
        <Route path="/mis-consultas/:id" element={<ResponseDetail />} />
        {/* Nueva ruta protegida para admin */}
        <Route path="/admin" element={<AdminDashboard />} />
      </Route>

      <Route path="*" element={<Navigate to="/login" replace />} />
    </Routes>
  );
}
