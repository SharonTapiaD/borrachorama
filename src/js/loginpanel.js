document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('loginForm');
  if (!form) return;

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const user = document.getElementById('username')?.value?.trim();
    const pass = document.getElementById('password')?.value;

    if (user === 'user' && pass === '1234') {
      window.location.href = 'User.html';
      return;
    }

    if (user === 'admin' && pass === '1234') {
      window.location.href = 'Administrador.html';
      return;
    }

    alert('Credenciales incorrectas.');
  });
});