import { render, screen } from '@testing-library/react';
import App from './App';

test('renders STAFT 認証タイトル', () => {
  render(<App />);
  const titleElement = screen.getByText(/STAFT 認証/i);
  expect(titleElement).toBeInTheDocument();
});
