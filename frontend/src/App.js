import { useCallback, useState } from 'react';
import './App.css';
import { API_BASE_URL } from './apiConfig';

function App() {
  const [mode, setMode] = useState('email');
  const [identifier, setIdentifier] = useState('');
  const [password, setPassword] = useState('');
  const [message, setMessage] = useState('');
  const [user, setUser] = useState(null);

  const handleLogin = useCallback(async (event) => {
    event.preventDefault();
    setMessage('');

    if (!identifier || !password) {
      setMessage('ID/メールまたはパスワードを入力してください');
      return;
    }

    try {
      const res = await fetch(`${API_BASE_URL}/postInfo/auth/login.php`, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ mode, identifier, password }),
      });

      const payload = await res.json();
      if (!res.ok || payload.success === false) {
        setMessage(payload.error || 'ログインに失敗しました');
        setUser(null);
        return;
      }

      setMessage('ログイン成功');
      setUser(payload.data);
    } catch (err) {
      setMessage('通信エラー: ' + err.message);
      setUser(null);
    }
  }, [mode, identifier, password]);

  const handleLogout = useCallback(async () => {
    try {
      const res = await fetch(`${API_BASE_URL}/postInfo/auth/logout.php`, {
        method: 'POST',
        credentials: 'include',
      });
      const payload = await res.json();

      if (res.ok && payload.success) {
        setMessage('ログアウトしました');
        setUser(null);
      } else {
        setMessage(payload.error || 'ログアウト失敗');
      }
    } catch (err) {
      setMessage('通信エラー: ' + err.message);
    }
  }, []);

  const fetchMe = useCallback(async () => {
    try {
      const res = await fetch(`${API_BASE_URL}/getInfo/auth/me.php`, {
        method: 'GET',
        credentials: 'include',
      });
      const payload = await res.json();
      if (res.ok && payload.success) {
        setUser(payload.data);
        setMessage('ユーザー情報取得成功');
      } else {
        setUser(null);
        setMessage(payload.error || '未ログイン');
      }
    } catch (err) {
      setMessage('通信エラー: ' + err.message);
    }
  }, []);

  return (
    <div className="app-container">
      <h1>STAFT 認証</h1>

      <div className="mode-switch">
        <button
          className={mode === 'email' ? 'active' : ''}
          onClick={() => setMode('email')}
        >
          メールでログイン
        </button>
        <button
          className={mode === 'username' ? 'active' : ''}
          onClick={() => setMode('username')}
        >
          IDでログイン
        </button>
      </div>

      <form className="login-form" onSubmit={handleLogin}>
        <label>
          {mode === 'email' ? 'メールアドレス' : 'ユーザーID'}
          <input
            type={mode === 'email' ? 'email' : 'text'}
            value={identifier}
            onChange={e => setIdentifier(e.target.value)}
            placeholder={mode === 'email' ? 'sample@example.com' : 'sampleuser'}
            required
          />
        </label>
        <label>
          パスワード
          <input
            type='password'
            value={password}
            onChange={e => setPassword(e.target.value)}
            placeholder='Password123!'
            required
          />
        </label>
        <button type='submit'>ログイン</button>
      </form>

      <div className='controls'>
        <button onClick={fetchMe}>ログイン状態を確認</button>
        <button onClick={handleLogout}>ログアウト</button>
      </div>

      {message && <p className='message'>{message}</p>}

      {user && (
        <div className='user-card'>
          <h2>ログイン中ユーザー</h2>
          <p>ID: {user.id}</p>
          <p>ユーザー名: {user.username}</p>
          <p>メール: {user.email}</p>
        </div>
      )}

      <div className='hint'>
        <p>初期サンプルユーザー:</p>
        <p>e-mail: sample@example.com / ID: sampleuser / password: Password123!</p>
      </div>
    </div>
  );
}

export default App;
