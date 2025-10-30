// Importa as funções que queremos testar
const { formatarCPF, formatarTelefone } = require('../../public/js/formatters');

describe('Testes de Formatação do Frontend', () => {

  test('deve formatar um CPF corretamente', () => {
    const input = '12345678900';
    const expected = '123.456.789-00';
    expect(formatarCPF(input)).toBe(expected);
  });

  test('deve formatar um telefone (celular) corretamente', () => {
    const input = '84999887766';
    const expected = '(84) 99988-7766';
    expect(formatarTelefone(input)).toBe(expected);
  });
});
