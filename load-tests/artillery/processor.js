// Processor para Artillery - funções auxiliares
module.exports = {
  generateRandomString: () => {
    return Math.random().toString(36).substring(2, 15);
  },
  generateRandomInt: (min, max) => {
    return Math.floor(Math.random() * (max - min + 1)) + min;
  }
};
