class donation_components {
  static close(token, type) {
    donationData[type][token].close();
    donationData[type][token] = null;
  }
}

module.exports = donation_components;
