#!/usr/bin/env node

/**
 * 認証APIのテストスクリプト
 * Usage: node testAuth.js
 */

const http = require('http');

// テスト用アカウント情報
const testAccounts = [
  { username: 'sampleuser', email: 'sample@example.com', password: 'Password123!', label: 'サンプル' },
  { username: 'testuser', email: 'test@example.com', password: 'Test123!', label: 'テスト用' },
  { username: 'devuser', email: 'dev@example.com', password: 'Dev123!', label: '開発者用' },
  { username: 'admin', email: 'admin@example.com', password: 'Admin123!', label: '管理者' },
];

const API_BASE = 'http://localhost:8080/api';

/**
 * HTTPリクエストを送信する共通関数
 * @param {string} endpoint APIエンドポイント
 * @param {string} method HTTPメソッド
 * @param {Object|null} data POSTデータ
 * @param {string} cookies セッションCookie
 * @returns {Promise<{data: any, cookies: string}>}
 */
async function request(endpoint, method = 'GET', data = null, cookies = '') {
  return new Promise((resolve, reject) => {
    const url = new URL(endpoint, API_BASE);
    const options = {
      hostname: url.hostname,
      port: url.port || 80,
      path: url.pathname + url.search,
      method: method,
      headers: {
        'Content-Type': 'application/json',
      },
    };

    if (cookies) {
      options.headers['Cookie'] = cookies;
    }

    const req = http.request(options, (res) => {
      let body = '';
      res.on('data', (chunk) => { body += chunk; });
      res.on('end', () => {
        const cookies = res.headers['set-cookie'] || '';
        try {
          const jsonData = JSON.parse(body);
          resolve({ data: jsonData, cookies, status: res.statusCode });
        } catch (e) {
          resolve({ data: body, cookies, status: res.statusCode });
        }
      });
    });

    req.on('error', reject);

    if (data) {
      req.write(JSON.stringify(data));
    }
    req.end();
  });
}

/**
 * テスト実行
 */
async function runTests() {
  console.log('🧪 STAFT認証APIテスト開始\n');

  for (const account of testAccounts) {
    console.log(`\n📝 【${account.label}】 ユーザー: ${account.username}`);
    console.log('─'.repeat(60));

    try {
      // 1. ログイン (ID)
      console.log('1️⃣  IDでログイン...');
      const loginRes = await request('/postInfo/auth/login.php', 'POST', {
        mode: 'username',
        identifier: account.username,
        password: account.password,
      });

      if (loginRes.status !== 200 || !loginRes.data.success) {
        console.error('❌ ログイン失敗:', loginRes.data.error || loginRes.data);
        continue;
      }

      console.log('✅ ログイン成功');
      console.log('   ユーザーID:', loginRes.data.data.id);
      console.log('   メール:', loginRes.data.data.email);

      // 2. ログイン状態確認
      console.log('2️⃣  ログイン状態確認...');
      const meRes = await request('/getInfo/auth/me.php', 'GET', null, loginRes.cookies);

      if (meRes.status !== 200 || !meRes.data.success) {
        console.error('❌ 状態確認失敗:', meRes.data.error || meRes.data);
        continue;
      }

      console.log('✅ ログイン状態確認成功');
      console.log('   ID:', meRes.data.data.id);
      console.log('   ユーザー名:', meRes.data.data.username);

      // 3. メール with メールアドレスでログイン
      console.log('3️⃣  メールアドレスでログイン...');
      const emailLoginRes = await request('/postInfo/auth/login.php', 'POST', {
        mode: 'email',
        identifier: account.email,
        password: account.password,
      });

      if (emailLoginRes.status !== 200 || !emailLoginRes.data.success) {
        console.error('❌ メールログイン失敗:', emailLoginRes.data.error || emailLoginRes.data);
        continue;
      }

      console.log('✅ メールログイン成功');

      // 4. ログアウト
      console.log('4️⃣  ログアウト...');
      const logoutRes = await request('/postInfo/auth/logout.php', 'POST', null, emailLoginRes.cookies);

      if (logoutRes.status !== 200 || !logoutRes.data.success) {
        console.error('❌ ログアウト失敗:', logoutRes.data.error || logoutRes.data);
        continue;
      }

      console.log('✅ ログアウト成功');

      // 5. ログアウト後の確認
      console.log('5️⃣  ログアウト後のアクセステスト...');
      const failRes = await request('/getInfo/auth/me.php', 'GET', null, emailLoginRes.cookies);

      if (failRes.status === 401) {
        console.log('✅ 正しく401が返された（未認証）');
      } else {
        console.warn('⚠️  予期しないステータス:', failRes.status);
      }

    } catch (err) {
      console.error('❌ テスト中断:', err.message);
    }
  }

  console.log('\n' + '═'.repeat(60));
  console.log('✨ テスト完了');
}

// 実行
if (process.argv[2] === '--help') {
  console.log('Usage: node testAuth.js');
  console.log('\nテスト対象:');
  testAccounts.forEach(a => {
    console.log(`  - ${a.label}: ${a.username} / ${a.password}`);
  });
  process.exit(0);
}

runTests().catch(console.error);
