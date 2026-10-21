document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('secureAccountForm');
  if (!form) return;
  form.addEventListener('submit', (event) => {
    const name = form.full_name.value.trim();
    const username = form.username.value.trim();
    const email = form.email.value.trim();
    const mobile = form.mobile.value.trim();
    const pw = form.password.value;
    const confirm = form.confirm_password.value;
    const errors = [];
    if (!/^[\p{L}][\p{L} .'-]{2,99}$/u.test(name)) errors.push('Enter a valid full name (3–100 characters).');
    if (!/^[A-Za-z][A-Za-z0-9_]{2,39}$/.test(username)) errors.push('Enter a valid username (3–40 characters).');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) || email.length > 254) errors.push('Enter a valid email.');
    if (!/^[0-9]{10}$/.test(mobile)) errors.push('Enter a 10-digit mobile number.');
    if (pw.length < 8 || pw.length > 72 || !/[A-Z]/.test(pw) || !/[a-z]/.test(pw) || !/[0-9]/.test(pw) || !/[^A-Za-z0-9]/.test(pw)) errors.push('Enter a strong password with uppercase, lowercase, number and symbol.');
    if (pw !== confirm) errors.push('Passwords do not match.');
    if (errors.length) { event.preventDefault(); window.alert(errors.join('\n')); }
  });
});
