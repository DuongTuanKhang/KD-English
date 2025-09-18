/********************
 * Scrabble – Frontend
 ********************/
const SIZE = 15;
const CENTER = 7; // 0-index center
const boardEl = document.getElementById("board");
const rackEl = document.getElementById("rack");
const turnIndicator = document.getElementById("turnIndicator");
const p1ScoreEl = document.getElementById("p1Score");
const p2ScoreEl = document.getElementById("p2Score");
const bagCountEl = document.getElementById("bagCount");
const lastScoreEl = document.getElementById("lastScore");
const historyEl = document.getElementById("history");
const msgEl = document.getElementById("message");

let board = make2D(SIZE, "");
let fixed = make2D(SIZE, false);
let staging = []; // {r,c,letter,fromRackIndex}
let playerRack = [];
let aiRack = [];
let bag = [];
let turn = "player";
let p1Score = 0,
  p2Score = 0;
let firstMoveDone = false;

/* Letter distribution (classic-ish) */
const LETTERS = {
  A: { count: 9, score: 1 },
  B: { count: 2, score: 3 },
  C: { count: 2, score: 3 },
  D: { count: 4, score: 2 },
  E: { count: 12, score: 1 },
  F: { count: 2, score: 4 },
  G: { count: 3, score: 2 },
  H: { count: 2, score: 4 },
  I: { count: 9, score: 1 },
  J: { count: 1, score: 8 },
  K: { count: 1, score: 5 },
  L: { count: 4, score: 1 },
  M: { count: 2, score: 3 },
  N: { count: 6, score: 1 },
  O: { count: 8, score: 1 },
  P: { count: 2, score: 3 },
  Q: { count: 1, score: 10 },
  R: { count: 6, score: 1 },
  S: { count: 4, score: 1 },
  T: { count: 6, score: 1 },
  U: { count: 4, score: 1 },
  V: { count: 2, score: 4 },
  W: { count: 2, score: 4 },
  X: { count: 1, score: 8 },
  Y: { count: 2, score: 4 },
  Z: { count: 1, score: 10 },
};
function buildBag() {
  const arr = [];
  for (const [ch, { count, score }] of Object.entries(LETTERS)) {
    for (let i = 0; i < count; i++) arr.push({ ch, score });
  }
  for (let i = arr.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [arr[i], arr[j]] = [arr[j], arr[i]];
  }
  return arr;
}
function letterScore(ch) {
  return LETTERS[ch]?.score ?? 0;
}

/* Multipliers layout */
const MULT = make2D(SIZE, { lm: 1, wm: 1 });
const TW = [
  [0, 0],
  [0, 7],
  [0, 14],
  [7, 0],
  [7, 14],
  [14, 0],
  [14, 7],
  [14, 14],
];
const DW = [
  [1, 1],
  [2, 2],
  [3, 3],
  [4, 4],
  [13, 13],
  [12, 12],
  [11, 11],
  [10, 10],
  [1, 13],
  [2, 12],
  [3, 11],
  [4, 10],
  [10, 4],
  [11, 3],
  [12, 2],
  [13, 1],
  [7, 7],
];
const TL = [
  [1, 5],
  [1, 9],
  [5, 1],
  [5, 5],
  [5, 9],
  [5, 13],
  [9, 1],
  [9, 5],
  [9, 9],
  [9, 13],
  [13, 5],
  [13, 9],
];
const DL = [
  [0, 3],
  [0, 11],
  [2, 6],
  [2, 8],
  [3, 0],
  [3, 7],
  [3, 14],
  [6, 2],
  [6, 6],
  [6, 8],
  [6, 12],
  [7, 3],
  [7, 11],
  [8, 2],
  [8, 6],
  [8, 8],
  [8, 12],
  [11, 0],
  [11, 7],
  [11, 14],
  [12, 6],
  [12, 8],
  [14, 3],
  [14, 11],
];
TW.forEach(([r, c]) => (MULT[r][c] = { lm: 1, wm: 3 }));
DW.forEach(([r, c]) => (MULT[r][c] = { lm: 1, wm: 2 }));
TL.forEach(([r, c]) => (MULT[r][c] = { lm: 3, wm: 1 }));
DL.forEach(([r, c]) => (MULT[r][c] = { lm: 2, wm: 1 }));

/* UI build */
function make2D(n, val) {
  return Array.from({ length: n }, () =>
    Array.from({ length: n }, () => JSON.parse(JSON.stringify(val)))
  );
}
function drawBoard() {
  boardEl.innerHTML = "";
  for (let r = 0; r < SIZE; r++) {
    for (let c = 0; c < SIZE; c++) {
      const cell = document.createElement("div");
      cell.className = "cell";
      const m = MULT[r][c];
      if (m.wm === 3) cell.classList.add("tw");
      else if (m.wm === 2) cell.classList.add("dw");
      else if (m.lm === 3) cell.classList.add("tl");
      else if (m.lm === 2) cell.classList.add("dl");
      if (!firstMoveDone && r === CENTER && c === CENTER)
        cell.classList.add("center");

      cell.dataset.r = r;
      cell.dataset.c = c;
      cell.addEventListener("click", () => onCellClick(r, c));
      cell.addEventListener("dragover", (e) => e.preventDefault());
      cell.addEventListener("drop", (e) => {
        e.preventDefault();
        const data = e.dataTransfer.getData("text/plain");
        if (data.startsWith("rack:")) {
          const idx = parseInt(data.split(":")[1], 10);
          placeFromRackTo(r, c, idx);
        }
      });
      renderCell(cell, r, c);
      boardEl.appendChild(cell);
    }
  }
}
function renderCell(cell, r, c) {
  const ch = board[r][c];
  const staged = staging.find((s) => s.r === r && s.c === c);
  cell.innerHTML = "";
  if (ch) {
    cell.appendChild(makeTile(ch, letterScore(ch)));
  } else if (staged) {
    const t = makeTile(staged.letter, letterScore(staged.letter));
    t.classList.add("ghost");
    cell.appendChild(t);
  } else {
    const m = MULT[r][c];
    if (m.wm === 3) cell.textContent = "TW";
    else if (m.wm === 2) cell.textContent = "DW";
    else if (m.lm === 3) cell.textContent = "TL";
    else if (m.lm === 2) cell.textContent = "DL";
  }
}
function makeTile(ch, sc) {
  const d = document.createElement("div");
  d.className = "tile";
  d.innerHTML = `<div>${ch}</div><div class="score">${sc}</div>`;
  return d;
}
function drawRack() {
  rackEl.innerHTML = "";
  playerRack.forEach((t, i) => {
    if (!t) return;
    const d = makeTile(t.ch, t.score);
    d.draggable = true;
    d.addEventListener("click", () => selectFromRack(i));
    d.addEventListener("dragstart", (e) =>
      e.dataTransfer.setData("text/plain", "rack:" + i)
    );
    rackEl.appendChild(d);
  });
}

/* Interactions */
let selectedRackIndex = null;
function selectFromRack(i) {
  selectedRackIndex = i;
  info("Chọn 1 ô trên bàn để đặt.");
}
function placeFromRackTo(r, c, idx) {
  if (turn !== "player") return;
  if (board[r][c]) {
    info("Ô này đã có chữ.");
    return;
  }
  const letter = playerRack[idx]?.ch;
  if (!letter) return;
  staging = staging.filter((s) => !(s.r === r && s.c === c));
  staging.push({ r, c, letter, fromRackIndex: idx });
  selectedRackIndex = null;
  updateAll();
}
function onCellClick(r, c) {
  if (turn !== "player") return;
  if (board[r][c]) {
    info("Ô này đã có chữ.");
    return;
  }
  if (selectedRackIndex == null) {
    info("Chọn 1 chữ ở rack trước.");
    return;
  }
  placeFromRackTo(r, c, selectedRackIndex);
}

/* Buttons */
document.getElementById("btnRecall").onclick = () => {
  staging.forEach((s) => {
    if (playerRack[s.fromRackIndex] == null)
      playerRack[s.fromRackIndex] = {
        ch: s.letter,
        score: letterScore(s.letter),
      };
  });
  staging = [];
  updateAll();
};
document.getElementById("btnShuffle").onclick = () => {
  playerRack = playerRack.sort(() => Math.random() - 0.5);
  updateAll();
};
document.getElementById("btnSwap").onclick = () => {
  const can = Math.min(3, bag.length);
  for (let i = 0; i < can; i++) {
    const idx = playerRack.findIndex((t) => t != null);
    if (idx < 0) break;
    const out = playerRack[idx];
    playerRack[idx] = bag.pop();
    bag.unshift(out);
  }
  addHistory("You", "Swapped tiles.");
  endTurnToAI();
};
document.getElementById("btnNew").onclick = () => newGame();
document.getElementById("btnSubmit").onclick = () => submitPlayer();

/* Submit: validate geometry + build all words + check with PHP + score */
async function submitPlayer() {
  if (turn !== "player") return;
  if (staging.length === 0) {
    info("Bạn chưa đặt chữ nào.");
    return;
  }

  const line = sameLine(staging);
  if (!line) {
    return error("Phải cùng 1 hàng hoặc 1 cột.");
  }

  const cells = lineCells(staging, line);
  if (!contiguous(cells, line)) {
    return error("Các chữ phải liên tục.");
  }

  if (!firstMoveDone) {
    if (!cells.some(({ r, c }) => r === CENTER && c === CENTER))
      return error("Nước đầu phải qua ô giữa ⭐.");
  } else {
    if (!touchesExisting(cells)) return error("Từ phải nối với chữ có sẵn.");
  }

  // Build main + cross words
  const built = buildWordsForSubmission(cells, line);
  if (built.main.word.length < 2) {
    return error("Từ tối thiểu 2 ký tự.");
  }

  // Ask backend (Gemini) to verify all words
  const toCheck = [built.main.word, ...built.cross.map((x) => x.word)].filter(
    (v, i, a) => a.indexOf(v) === i
  );
  const okMap = await checkWordsGemini(toCheck);
  const anyInvalid = toCheck.some((w) => !okMap[w]);

  if (anyInvalid) return error("Một hoặc nhiều từ không hợp lệ.");

  // Score
  let total = scoreWordCells(built.main.cells, line);
  for (const cw of built.cross) total += scoreWordCells(cw.cells, cw.line);

  // Commit
  cells.forEach(({ r, c, letter }) => {
    board[r][c] = letter;
    fixed[r][c] = true;
  });
  staging.forEach((s) => (playerRack[s.fromRackIndex] = null));
  staging = [];
  refillRack(playerRack);

  firstMoveDone = true;
  p1Score += total;
  p1ScoreEl.textContent = p1Score;
  lastScoreEl.textContent = total;
  addHistory("You", `${built.main.word} • +${total}`);
  info(`Bạn đặt: ${built.main.word} (+${total})`);
  updateAll();
  endTurnToAI();
}

function endTurnToAI() {
  turn = "ai";
  turnIndicator.textContent = "AI's Turn";
  setTimeout(aiMove, 250);
}

/* Geometry helpers */
function sameLine(arr) {
  const r0 = arr[0].r,
    c0 = arr[0].c;
  const sameRow = arr.every((s) => s.r === r0);
  const sameCol = arr.every((s) => s.c === c0);
  return sameRow
    ? { type: "row", r: r0 }
    : sameCol
    ? { type: "col", c: c0 }
    : null;
}
function lineCells(staging, line) {
  if (line.type === "row") {
    const r = line.r;
    const minC = Math.min(...staging.map((s) => s.c)),
      maxC = Math.max(...staging.map((s) => s.c));
    const cells = [];
    for (let c = minC; c <= maxC; c++) {
      const letter =
        board[r][c] ||
        staging.find((s) => s.r === r && s.c === c)?.letter ||
        "";
      if (letter) cells.push({ r, c, letter });
    }
    return cells;
  } else {
    const c = line.c;
    const minR = Math.min(...staging.map((s) => s.r)),
      maxR = Math.max(...staging.map((s) => s.r));
    const cells = [];
    for (let r = minR; r <= maxR; r++) {
      const letter =
        board[r][c] ||
        staging.find((s) => s.r === r && s.c === c)?.letter ||
        "";
      if (letter) cells.push({ r, c, letter });
    }
    return cells;
  }
}
function contiguous(cells, line) {
  if (line.type === "row") {
    const cols = cells.map((x) => x.c).sort((a, b) => a - b);
    for (let i = 1; i < cols.length; i++)
      if (cols[i] !== cols[i - 1] + 1) return false;
  } else {
    const rows = cells.map((x) => x.r).sort((a, b) => a - b);
    for (let i = 1; i < rows.length; i++)
      if (rows[i] !== rows[i - 1] + 1) return false;
  }
  return true;
}
function touchesExisting(cells) {
  for (const { r, c } of cells) {
    const dirs = [
      [1, 0],
      [-1, 0],
      [0, 1],
      [0, -1],
    ];
    for (const [dr, dc] of dirs) {
      const nr = r + dr,
        nc = c + dc;
      if (nr >= 0 && nc >= 0 && nr < SIZE && nc < SIZE && fixed[nr][nc])
        return true;
    }
  }
  return false;
}

/* Build words: main + cross for scoring/checking */
function buildWordsForSubmission(mainCells, line) {
  // sort main cells
  if (line.type === "row") mainCells.sort((a, b) => a.c - b.c);
  else mainCells.sort((a, b) => a.r - b.r);

  // Expand main word to both ends including existing letters
  let allMain = [];
  if (line.type === "row") {
    const r = line.r;
    let cStart = mainCells[0].c,
      cEnd = mainCells[mainCells.length - 1].c;
    while (cStart > 0 && board[r][cStart - 1]) cStart--;
    while (cEnd < SIZE - 1 && board[r][cEnd + 1]) cEnd++;
    for (let c = cStart; c <= cEnd; c++) {
      const letter = board[r][c] || mainCells.find((s) => s.c === c)?.letter;
      if (letter) allMain.push({ r, c, letter });
    }
  } else {
    const c = line.c;
    let rStart = mainCells[0].r,
      rEnd = mainCells[mainCells.length - 1].r;
    while (rStart > 0 && board[rStart - 1][c]) rStart--;
    while (rEnd < SIZE - 1 && board[rEnd + 1][c]) rEnd++;
    for (let r = rStart; r <= rEnd; r++) {
      const letter = board[r][c] || mainCells.find((s) => s.r === r)?.letter;
      if (letter) allMain.push({ r, c, letter });
    }
  }
  const mainWord = allMain.map((x) => x.letter).join("");

  // Cross words for each newly placed tile
  const crosses = [];
  for (const s of staging) {
    if (line.type === "row") {
      // build vertical word crossing s.r,s.c
      let cells = [];
      let r = s.r;
      let r0 = r;
      while (r0 > 0 && board[r0 - 1][s.c]) r0--;
      let r1 = r;
      while (r1 < SIZE - 1 && board[r1 + 1][s.c]) r1++;
      if (r1 !== r0) {
        // at least 2 letters
        for (let rr = r0; rr <= r1; rr++) {
          const letter =
            staging.find((t) => t.r === rr && t.c === s.c)?.letter ||
            board[rr][s.c];
          cells.push({ r: rr, c: s.c, letter });
        }
        crosses.push({
          word: cells.map((x) => x.letter).join(""),
          cells,
          line: { type: "col", c: s.c },
        });
      }
    } else {
      // build horizontal
      let cells = [];
      let c = s.c;
      let c0 = c;
      while (c0 > 0 && board[s.r][c0 - 1]) c0--;
      let c1 = c;
      while (c1 < SIZE - 1 && board[s.r][c1 + 1]) c1++;
      if (c1 !== c0) {
        for (let cc = c0; cc <= c1; cc++) {
          const letter =
            staging.find((t) => t.r === s.r && t.c === cc)?.letter ||
            board[s.r][cc];
          cells.push({ r: s.r, c: cc, letter });
        }
        crosses.push({
          word: cells.map((x) => x.letter).join(""),
          cells,
          line: { type: "row", r: s.r },
        });
      }
    }
  }
  return { main: { word: mainWord, cells: allMain }, cross: crosses };
}

/* Scoring for a word given its cells and line */
function scoreWordCells(cells, line) {
  let total = 0,
    wordMult = 1;
  for (const { r, c, letter } of cells) {
    let add = letterScore(letter);
    if (!fixed[r][c]) {
      add *= MULT[r][c].lm;
      wordMult *= MULT[r][c].wm;
    }
    total += add;
  }
  return total * wordMult;
}

/* Dictionary (Gemini) */
async function checkWordsGemini(words) {
  try {
    const res = await fetch("check-word.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ words }),
    });
    const data = await res.json();
    // Expect: { ok: true, results: { WORD: true/false, ... } }
    if (data && data.ok && data.results) return data.results;
    return Object.fromEntries(words.map((w) => [w, false]));
  } catch (e) {
    console.error(e);
    return Object.fromEntries(words.map((w) => [w, false]));
  }
}

/* AI (very simple) */
async function aiMove() {
  // If board empty, try GO
  if (
    !firstMoveDone &&
    aiRack.some((t) => t?.ch === "G") &&
    aiRack.some((t) => t?.ch === "O")
  ) {
    placeAIMove("GO", CENTER, CENTER, "horizontal");
    addHistory("AI", "GO • +4");
    p2Score += 4;
    p2ScoreEl.textContent = p2Score;
    lastScoreEl.textContent = 4;
    firstMoveDone = true;
    info("AI đặt: GO (+4)");
  } else {
    addHistory("AI", "Skip.");
    info("AI bỏ lượt.");
  }
  turn = "player";
  turnIndicator.textContent = "Your Turn";
  updateAll();
}
function placeAIMove(word, row, col, dir) {
  const cells = [];
  for (let i = 0; i < word.length; i++) {
    const r = dir === "horizontal" ? row : row + i;
    const c = dir === "horizontal" ? col + i : col;
    cells.push({ r, c, letter: word[i] });
  }
  const line =
    dir === "horizontal" ? { type: "row", r: row } : { type: "col", c: col };
  const score = scoreWordCells(cells, line);
  cells.forEach(({ r, c, letter }) => {
    board[r][c] = letter;
    fixed[r][c] = true;
  });
  const needs = cells.filter(({ r, c }) => !fixed[r][c]).map((x) => x.letter);
  needs.forEach((l) => {
    const idx = aiRack.findIndex((t) => t && t.ch === l);
    if (idx >= 0) aiRack[idx] = null;
  });
  refillRack(aiRack);
}

/* Rack & bag */
function refillRack(rack) {
  for (let i = 0; i < rack.length; i++)
    if (rack[i] == null && bag.length) rack[i] = bag.pop();
  while (rack.length < 7 && bag.length) rack.push(bag.pop());
}
function startRacks() {
  playerRack = [];
  aiRack = [];
  for (let i = 0; i < 7; i++) playerRack.push(bag.pop());
  for (let i = 0; i < 7; i++) aiRack.push(bag.pop());
}

/* History & messages */
function addHistory(who, text) {
  const row = document.createElement("div");
  row.className = "row";
  row.innerHTML = `<strong>${who}</strong> — ${text}`;
  historyEl.prepend(row);
}
function info(t) {
  msgEl.textContent = t;
  msgEl.classList.remove("error");
}
function error(t) {
  msgEl.textContent = t;
  msgEl.classList.add("error");
}

/* Mini dictionary (local) */
const miniDict = new Set([
  "GO",
  "DO",
  "TO",
  "BE",
  "AT",
  "LEARN",
  "READ",
  "WRITE",
  "PLAY",
  "GAME",
  "WORD",
  "CAR",
  "CAT",
  "DOG",
  "BEE",
  "TEA",
  "EEL",
]);
document.getElementById("dictQuery").addEventListener("input", (e) => {
  const v = e.target.value.trim().toUpperCase();
  document.getElementById("dictResult").textContent =
    v && miniDict.has(v) ? `✅ "${v}" found.` : v ? `❌ "${v}" not found.` : "";
});

/* New game + render */
function newGame() {
  board = make2D(SIZE, "");
  fixed = make2D(SIZE, false);
  staging = [];
  firstMoveDone = false;
  p1Score = 0;
  p2Score = 0;
  p1ScoreEl.textContent = "0";
  p2ScoreEl.textContent = "0";
  lastScoreEl.textContent = "0";
  bag = buildBag();
  startRacks();
  turn = "player";
  turnIndicator.textContent = "Your Turn";
  historyEl.innerHTML = "";
  info("Bắt đầu ván mới. Bạn đi trước.");
  updateAll();
}
function updateAll() {
  drawBoard();
  drawRack();
  bagCountEl.textContent = bag.length;
}

/* Init */
newGame();
