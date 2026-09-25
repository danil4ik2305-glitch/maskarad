// ===== Маска ввода телефона: +7 (XXX) XXX-XX-XX =====
document.querySelectorAll('input[data-phone]').forEach(inp => {
  inp.addEventListener('input', () => {
    let d = inp.value.replace(/\D/g, '');
    if (d.startsWith('7') || d.startsWith('8')) d = d.slice(1);
    d = d.slice(0, 10);
    let r = '+7';
    if (d.length) r += ' (' + d.slice(0, 3);
    if (d.length >= 3) r += ')';
    if (d.length > 3) r += ' ' + d.slice(3, 6);
    if (d.length > 6) r += '-' + d.slice(6, 8);
    if (d.length > 8) r += '-' + d.slice(8, 10);
    inp.value = r;
  });
});

// ===== Клиентская валидация форм =====
const rules = {
  required: v => v.trim() !== '' || 'Поле обязательно для заполнения',
  email: v => /^[^\s@]+@[^\s@]+\.[a-z]{2,}$/i.test(v) || 'Введите корректный e-mail',
  phone: v => /^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/.test(v) || 'Введите телефон полностью',
  password: v => /^(?=.*\d)(?=.*[a-zA-Z])[a-zA-Z\d]{8,}$/.test(v) || 'Не менее 8 символов: латинские буквы и цифры',
  name: v => /^[А-Яа-яЁёA-Za-z\- ]{2,50}$/.test(v) || 'Только буквы, от 2 символов',
};
function showErr(inp, msg) {
  let h = inp.nextElementSibling;
  if (!h || !h.classList.contains('hint')) { h = document.createElement('div'); h.className = 'hint'; inp.after(h); }
  h.textContent = msg || '';
  inp.classList.toggle('invalid', !!msg);
}
document.querySelectorAll('form[data-validate]').forEach(form => {
  // запрет ввода цифр в поля имени
  form.querySelectorAll('[data-rules~="name"]').forEach(i => i.addEventListener('input', () => { i.value = i.value.replace(/[^А-Яа-яЁёA-Za-z\- ]/g, ''); }));
  form.addEventListener('submit', ev => {
    let ok = true;
    form.querySelectorAll('[data-rules]').forEach(inp => {
      let msg = '';
      for (const r of inp.dataset.rules.split(' ')) {
        if (r !== 'required' && inp.value.trim() === '') continue;
        const res = rules[r](inp.value);
        if (res !== true) { msg = res; break; }
      }
      if (inp.dataset.match) {
        const other = form.querySelector('[name="' + inp.dataset.match + '"]');
        if (!msg && other.value !== inp.value) msg = 'Пароли не совпадают';
      }
      showErr(inp, msg);
      if (msg) ok = false;
    });
    if (!ok) ev.preventDefault();
  });
});

// ===== Календарь бронирования =====
const cal = document.getElementById('calendar');
if (cal) {
  const busy = JSON.parse(cal.dataset.busy || '[]');
  const price = +cal.dataset.price;
  const fromInp = document.getElementById('date_from'), toInp = document.getElementById('date_to');
  let month = new Date(); month.setDate(1);
  let from = null, to = null;
  const iso = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  const today = iso(new Date());
  function render() {
    const y = month.getFullYear(), m = month.getMonth();
    document.getElementById('cal-title').textContent = month.toLocaleString('ru', { month: 'long', year: 'numeric' });
    let html = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'].map(d => `<div class="h">${d}</div>`).join('');
    const shift = (new Date(y, m, 1).getDay() + 6) % 7;
    html += '<div class="h"></div>'.repeat(shift);
    const days = new Date(y, m + 1, 0).getDate();
    for (let i = 1; i <= days; i++) {
      const s = iso(new Date(y, m, i));
      let cls = busy.includes(s) || s < today ? 'busy' : 'free';
      if (s === from || s === to) cls = 'sel'; else if (from && to && s > from && s < to) cls = 'rng';
      html += `<div class="${cls}" data-d="${s}">${i}</div>`;
    }
    cal.innerHTML = html;
  }
  cal.addEventListener('click', e => {
    const d = e.target.dataset.d;
    if (!d || e.target.classList.contains('busy')) return;
    if (!from || to || d < from) { from = d; to = null; }
    else {
      // диапазон не должен пересекать занятые даты
      if (busy.some(b => b > from && b < d)) { alert('В выбранном периоде есть занятые даты'); return; }
      to = d;
    }
    fromInp.value = from || ''; toInp.value = to || from || '';
    const n = to ? Math.round((new Date(to) - new Date(from)) / 864e5) + 1 : 1;
    const paid = n >= 2 ? n - 1 : n; // вторые сутки в подарок
    document.getElementById('sum').textContent = (paid * price).toLocaleString('ru') + ' ₽ за ' + n + ' сут.';
    render();
  });
  document.getElementById('prev').onclick = () => { month.setMonth(month.getMonth() - 1); render(); };
  document.getElementById('next').onclick = () => { month.setMonth(month.getMonth() + 1); render(); };
  render();
}

// ===== API: ближайшие праздники (Nager.Date) =====
const hol = document.getElementById('holidays');
if (hol) {
  const year = new Date().getFullYear();
  const tips = ['Новогодние костюмы', 'Карнавальные образы', 'Тематические костюмы', 'Исторические костюмы'];
  Promise.all([year, year + 1].map(y => fetch(`https://date.nager.at/api/v3/PublicHolidays/${y}/RU`).then(r => r.json())))
    .then(([a, b]) => {
      const today = new Date().toISOString().slice(0, 10);
      const list = a.concat(b).filter(h => h.date >= today).slice(0, 4);
      hol.innerHTML = list.map((h, i) => `<div class="holiday"><b>${new Date(h.date).toLocaleDateString('ru', { day: 'numeric', month: 'long' })}</b>${h.localName}<br><a href="catalog.php" style="color:var(--wine);font-weight:600;font-size:14px">${tips[i % 4]} →</a></div>`).join('');
    })
    .catch(() => { hol.innerHTML = '<p>Не удалось загрузить календарь праздников.</p>'; });
}
