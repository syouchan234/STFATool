import React from 'react';
import { Container, Typography, Button } from '@mui/material';

const OutputPage = () => {
  const handleExport = () => {
    // Excel出力API呼び出し
    alert('Excel出力機能は未実装');
  };

  return (
    <Container>
      <Typography variant="h4">帳票出力</Typography>
      <Button variant="contained" onClick={handleExport}>Excel出力</Button>
    </Container>
  );
};

export default OutputPage;