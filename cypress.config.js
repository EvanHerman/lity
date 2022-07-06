const { defineConfig } = require( "cypress" );

module.exports = defineConfig( {
  chromeWebSecurity: false,
  fileServerFolder: ".dev/tests/cypress",
  fixturesFolder: ".dev/tests/cypress/fixtures",
  screenshotsFolder: ".dev/tests/cypress/screenshots",
  videosFolder: ".dev/tests/cypress/videos",
  projectId: "b5ufbf",
  viewportWidth: 1920,
  viewportHeight: 1080,

  env: {
    localTestURL: "https://taxonomy-ordering.wp/",
    wpUsername: "evan",
    wpPassword: "evan",
    templateLang: "en_US",
  },

  retries: {
    runMode: 0,
    openMode: 0,
  },

  e2e: {
    specPattern: ".dev/tests/cypress/e2e/*.cy.js",
    supportFile: ".dev/tests/cypress/support/e2e.js",
  },
} );
