# API仕様

全APIは `api/` 配下で動作。JSON入出力で統一。

- GET `/api/getInfo/getStaffList.php`
  - req: header `Authorization` (セッション Cookie)
  - resp: `[{id, name, role, active}, ...]`

- GET `/api/getInfo/getShifts.php`
  - req: `startDate`, `endDate`, `staffId?`
  - resp: `[{id, staffId, date, startTime, endTime, note}, ...]`
  - 要件: 参照可能期間を「当月から前後2ヶ月」と制限（例: 4月なら2〜6月）

- POST `/api/postInfo/saveStaff.php`
  - req: `{id?, name, role, email, password?}`
  - resp: `{success: true, id: ...}`

- POST `/api/postInfo/setShift.php`
  - req: `{id?, staffId, date, startTime, endTime, memo}`
  - validation: 開始<終了、重複禁止

- POST `/api/postInfo/saveRequest.php`
  - req: `{staffId, date, requestType: 'holiday'|'off'|'work', message?}`
  - 対象: 認証ユーザー自身のみ

- POST `/api/auth/login.php`
  - req: `{email, password}`
  - resp: `{success: true, user: {id, name, role}}`