
// Basic interactions for +/- counters and password toggle
document.addEventListener('click', (e) => {
  if (e.target.matches('.counter .plus')) {
    const input = e.target.parentElement.querySelector('input[type=number]');
    input.value = parseInt(input.value || 0, 10) + 1;
  }
  if (e.target.matches('.counter .minus')) {
    const input = e.target.parentElement.querySelector('input[type=number]');
    const next = Math.max(1, parseInt(input.value || 1, 10) - 1);
    input.value = next;
  }
  if (e.target.matches('.toggle-eye')) {
    const input = e.target.parentElement.querySelector('input');
    input.type = input.type === 'password' ? 'text' : 'password';
  }
});
