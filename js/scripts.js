function handleFirstTab(e) {
  if (e.keyCode === 9) {
    document.body.classList.add("is-tabbing");
    document.body.classList.remove("is-not-tabbing");
    window.removeEventListener("keydown", handleFirstTab);
  }
}

window.addEventListener("keydown", handleFirstTab);
