import React, { useState, useEffect } from 'react';
import { Container, Typography, Button, Modal, TextField, MenuItem, Table, TableBody, TableCell, TableHead, TableRow, Box } from '@mui/material';
import { API_BASE_URL } from '../apiConfig';

const StaffListPage = () => {
  const [staff, setStaff] = useState([]);
  const [open, setOpen] = useState(false);
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [role, setRole] = useState('一般');
  const [password, setPassword] = useState('');

  useEffect(() => {
    fetchStaff();
  }, []);

  const fetchStaff = async () => {
    const res = await fetch(`${API_BASE_URL}/getInfo/getStaffList.php`, {
      credentials: 'include',
    });
    const data = await res.json();
    if (data.success) {
      setStaff(data.data);
    }
  };

  const handleSave = async () => {
    const res = await fetch(`${API_BASE_URL}/postInfo/saveStaff.php`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name, email, role, password }),
    });
    const data = await res.json();
    if (data.success) {
      setOpen(false);
      fetchStaff();
    }
  };

  return (
    <Container>
      <Typography variant="h4">スタッフ管理</Typography>
      <Button variant="contained" onClick={() => setOpen(true)}>スタッフ追加</Button>
      <Table>
        <TableHead>
          <TableRow>
            <TableCell>ID</TableCell>
            <TableCell>名前</TableCell>
            <TableCell>ロール</TableCell>
            <TableCell>アクティブ</TableCell>
          </TableRow>
        </TableHead>
        <TableBody>
          {staff.map(s => (
            <TableRow key={s.id}>
              <TableCell>{s.id}</TableCell>
              <TableCell>{s.name}</TableCell>
              <TableCell>{s.role}</TableCell>
              <TableCell>{s.active ? 'はい' : 'いいえ'}</TableCell>
            </TableRow>
          ))}
        </TableBody>
      </Table>
      <Modal open={open} onClose={() => setOpen(false)}>
        <Box sx={{ position: 'absolute', top: '50%', left: '50%', transform: 'translate(-50%, -50%)', width: 400, bgcolor: 'background.paper', p: 4 }}>
          <Typography variant="h6">スタッフ登録</Typography>
          <TextField label="名前" value={name} onChange={(e) => setName(e.target.value)} fullWidth />
          <TextField label="メール" value={email} onChange={(e) => setEmail(e.target.value)} fullWidth />
          <TextField select label="ロール" value={role} onChange={(e) => setRole(e.target.value)} fullWidth>
            <MenuItem value="一般">一般</MenuItem>
            <MenuItem value="責任者">責任者</MenuItem>
            <MenuItem value="管理者">管理者</MenuItem>
          </TextField>
          <TextField label="パスワード" type="password" value={password} onChange={(e) => setPassword(e.target.value)} fullWidth />
          <Button onClick={handleSave}>保存</Button>
        </Box>
      </Modal>
    </Container>
  );
};

export default StaffListPage;