import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { ThemeProvider, createTheme } from '@mui/material/styles';
import CssBaseline from '@mui/material/CssBaseline';
import AppBar from '@mui/material/AppBar';
import Toolbar from '@mui/material/Toolbar';
import Typography from '@mui/material/Typography';
import Button from '@mui/material/Button';
import { Link, useNavigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import LoginPage from './pages/LoginPage';
import StaffListPage from './pages/StaffListPage';
import ShiftCalendarPage from './pages/ShiftCalendarPage';
import OutputPage from './pages/OutputPage';
import ProtectedRoute from './components/ProtectedRoute';

const theme = createTheme();

const Navigation = () => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout();
    navigate('/login');
  };

  if (!user) return null;

  return (
    <AppBar position="static">
      <Toolbar>
        <Typography variant="h6" component="div" sx={{ flexGrow: 1 }}>
          SS-Shift
        </Typography>
        <Button color="inherit" component={Link} to="/shift">シフト</Button>
        {user.role === '管理者' && <Button color="inherit" component={Link} to="/staff">スタッフ</Button>}
        <Button color="inherit" component={Link} to="/report">帳票</Button>
        <Button color="inherit" onClick={handleLogout}>ログアウト</Button>
      </Toolbar>
    </AppBar>
  );
};

function App() {
  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <AuthProvider>
        <Router>
          <Navigation />
          <Routes>
            <Route path="/login" element={<LoginPage />} />
            <Route path="/staff" element={<ProtectedRoute><StaffListPage /></ProtectedRoute>} />
            <Route path="/shift" element={<ProtectedRoute><ShiftCalendarPage /></ProtectedRoute>} />
            <Route path="/report" element={<ProtectedRoute><OutputPage /></ProtectedRoute>} />
            <Route path="/" element={<Navigate to="/shift" replace />} />
          </Routes>
        </Router>
      </AuthProvider>
    </ThemeProvider>
  );
}

export default App;
