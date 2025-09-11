import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import ProtectedRoute from './components/ProtectedRoute';
import Login from './pages/Login';
import MyResponses from './pages/MyResponses';
import ResponseDetail from './pages/ResponseDetail';

export default function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <Routes>
          <Route path="/login" element={<Login />} />
          <Route path="/mis-consultas" element={
            <ProtectedRoute><MyResponses /></ProtectedRoute>
          }/>
          <Route path="/mis-consultas/:id" element={
            <ProtectedRoute><ResponseDetail /></ProtectedRoute>
          }/>
          <Route path="/" element={<Navigate to="/mis-consultas" replace />} />
          <Route path="*" element={<div style={{padding:24}}>404</div>} />
        </Routes>
      </BrowserRouter>
    </AuthProvider>
  );
}
