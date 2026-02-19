/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './templates/**/*.html.twig',
    './assets/react/**/*.jsx',
    './assets/react/**/*.js',
  ],
  theme: {
    extend: {
      fontFamily: {
        'thunder': ['"Thunder-Extra-Bold"', 'sans-serif'],
      },
    },
  },
  plugins: [],
}

