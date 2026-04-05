import React, { useState, useEffect } from 'react';
import { Container, Typography, Box, Button, Modal, TextField, MenuItem, Grid } from '@mui/material';
import { DatePicker } from '@mui/x-date-pickers/DatePicker';
import { TimePicker } from '@mui/x-date-pickers/TimePicker';
import { LocalizationProvider } from '@mui/x-date-pickers/LocalizationProvider';
import { AdapterDayjs } from '@mui/x-date-pickers/AdapterDayjs';
import { API_BASE_URL } from '../apiConfig';
import { useAuth } from '../contexts/AuthContext';

const ShiftCalendarPage = () => {
  const [shifts, setShifts] = useState([]);
  const [open, setOpen] = useState(false);
  const [selectedDate, setSelectedDate] = useState(null);
  const [startTime, setStartTime] = useState(null);
  const [endTime, setEndTime] = useState(null);
  const [memo, setMemo] = useState('');
  const { user } = useAuth();

  useEffect(() => {
    fetchShifts();
  }, []);

  const fetchShifts = async () => {
    const start = '2024-01-01';
    const end = '2024-12-31';
    const res = await fetch(`${API_BASE_URL}/getInfo/getShifts.php?startDate=${start}&endDate=${end}`, {
      credentials: 'include',
    });
    const data = await res.json();
    if (data.success) {
      setShifts(data.data);
    }
  };

  const handleSave = async () => {
    const res = await fetch(`${API_BASE_URL}/postInfo/setShift.php`, {
      method: 'POST',
      credentials: 'include',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        staffId: user.id,
        date: selectedDate.format('YYYY-MM-DD'),
        startTime: startTime.format('HH:mm'),
        endTime: endTime.format('HH:mm'),
        memo,
      }),
    });
    const data = await res.json();
    if (data.success) {
      setOpen(false);
      fetchShifts();
    }
  };

  return (
    <LocalizationProvider dateAdapter={AdapterDayjs}>
      <Container>
        <Typography variant="h4">シフト管理</Typography>
        <Button variant="contained" onClick={() => setOpen(true)}>シフト追加</Button>
        <Box sx={{ mt: 2 }}>
          {shifts.map(shift => (
            <Box key={shift.id} sx={{ p: 1, border: 1 }}>
              {shift.date} {shift.startTime} - {shift.endTime} {shift.memo}
            </Box>
          ))}
        </Box>
        <Modal open={open} onClose={() => setOpen(false)}>
          <Box sx={{ position: 'absolute', top: '50%', left: '50%', transform: 'translate(-50%, -50%)', width: 400, bgcolor: 'background.paper', p: 4 }}>
            <Typography variant="h6">シフト登録</Typography>
            <DatePicker label="日付" value={selectedDate} onChange={setSelectedDate} />
            <TimePicker label="開始時間" value={startTime} onChange={setStartTime} />
            <TimePicker label="終了時間" value={endTime} onChange={setEndTime} />
            <TextField label="メモ" value={memo} onChange={(e) => setMemo(e.target.value)} />
            <Button onClick={handleSave}>保存</Button>
          </Box>
        </Modal>
      </Container>
    </LocalizationProvider>
  );
};

export default ShiftCalendarPage;