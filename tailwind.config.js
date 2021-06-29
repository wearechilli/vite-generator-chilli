module.exports = {
  mode: 'jit',
  purge: [
    './templates/**/*.{html,twig}'
  ],
  darkMode: false, // or 'media' or 'class'
  theme: {
    extend: {
      fontFamily: {
        display: [
          'Lobster',
          'cursive'
        ]
      }
    },
  },
  variants: {
    extend: {},
  },
  plugins: [],
}
