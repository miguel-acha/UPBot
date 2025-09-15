// src/components/ProtectedRoute.jsx
import { Navigate, Outlet } from "react-router-dom";

export default function ProtectedRoute() {
  const token = localStorage.getItem("token");
  const isAuth = token && token !== "null" && token !== "undefined";

  return isAuth ? <Outlet /> : <Navigate to="/login" replace />;
}
