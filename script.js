$(document).ready(function () {

  $("#form-contato").on("submit", function (evento) {
    evento.preventDefault();

    var retorno = $("#retorno");
    var botao = $("#btn-enviar");

    retorno.removeClass("sucesso erro").text("");

    // --- validação no navegador ---
    var nome = $.trim($("#nome").val());
    var email = $.trim($("#email").val());
    var mensagem = $.trim($("#mensagem").val());

    if (nome === "" || email === "" || mensagem === "") {
      retorno.addClass("erro").text("Preencha todos os campos.");
      return;
    }

    if (mensagem.length < 10) {
      retorno.addClass("erro").text("Escreva uma mensagem com pelo menos 10 caracteres.");
      return;
    }

    // --- envio ---
    botao.prop("disabled", true).text("Enviando...");

    $.post("api.php", $(this).serialize(), null, "json")

      .done(function (resposta) {
        if (resposta.ok) {
          retorno.addClass("sucesso").text(resposta.mensagem);
          $("#form-contato")[0].reset();
        } else {
          retorno.addClass("erro").text(resposta.mensagem);
        }
      })

      .fail(function () {
        retorno.addClass("erro")
               .text("Não foi possível falar com o servidor. Tente de novo.");
      })

      .always(function () {
        botao.prop("disabled", false).text("Enviar");
      });
  });

});
