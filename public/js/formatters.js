function formatarCPF(value) {
  value = value.replace(/\D/g, "");
  value = value.replace(/(\d{3})(\d)/, "$1.$2");
  value = value.replace(/(\d{3})(\d)/, "$1.$2");
  value = value.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
  return value;
}

function formatarTelefone(value) {
  value = value.replace(/\D/g, "");
  value = value.replace(/^(\d{2})(\d)/, "($1) $2");
  value = value.replace(/(\d{5})(\d{4})$/, "$1-$2");
  return value;
}

// Exporta as funções para que o Jest possa importá-las
module.exports = { formatarCPF, formatarTelefone };
