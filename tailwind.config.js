/** Javamaya Tailwind — compile: tools/build-css.sh (hasil dibundel di rilis, buyer tanpa build step) */
module.exports = {
  darkMode: 'class',
  content: ['./resources/views/**/*.blade.php', './installer/views/*.php'],
  theme: {
    extend: {
      colors: {
        accent: 'var(--jm-accent)',
        ink:    'var(--jm-ink)',
        paper:  'var(--jm-bg)',
        line:   'var(--jm-line)',
        muted:  'var(--jm-muted)',
      },
      fontFamily: { sans: ['Plus Jakarta Sans', 'Inter', 'system-ui', 'sans-serif'] },
      boxShadow: {
        'card': '0 1px 2px rgb(var(--jm-shadow) / 0.04), 0 8px 24px -12px rgb(var(--jm-shadow) / 0.12)',
        'card-hover': '0 2px 4px rgb(var(--jm-shadow) / 0.05), 0 16px 40px -12px rgb(var(--jm-shadow) / 0.2)',
      },
    },
  },
  safelist: [
    // badge status yang dirakit dari array PHP
    'bg-amber-50','text-amber-700','border-amber-200','bg-amber-100','text-amber-800',
    'bg-emerald-50','text-emerald-700','border-emerald-200','bg-emerald-100',
    'bg-rose-50','text-rose-700','border-rose-200','bg-rose-100',
    'bg-stone-100','text-stone-500','border-stone-200',
  ],
  plugins: [],
};
