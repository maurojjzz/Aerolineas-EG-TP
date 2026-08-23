const paisInput = document.getElementById("pais");
const paisResults = document.getElementById("paises-results");
const paisOptions = document.querySelectorAll(".pais-option");

paisInput.addEventListener("focus", function () {
    paisResults.style.display = "block";
});

paisOptions.forEach((option) => {

    option.addEventListener("click", function () {

        paisInput.value = this.dataset.nombre;

        paisResults.style.display = "none";

    });

});
