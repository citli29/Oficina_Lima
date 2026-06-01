//informacao das fichas
    utilizador(id, email, password, nome, foto_perfil_p, tipo_funcionario_id->tipo_funcionario);
    tipo_funcionario(id, designacao);
    viaturas(id, marca, modelo, matricula, nr_chassi, cilindrada, ano, mes, cod_motor, cod_cor);
    clientes(id, nome, nr_telm,nr_telf, morada, email, cod_postal, localidade, nif);
    folhas_servico(id, fatura_id->faturas(ou so o nr_fatura), data_entrada, data_saida, cliente_id->cliente, viatura_id->viaturas, desc_avaria, [material_aplicado_id->materiais_aplicados], desc_servico_efetuado, observacoes, [tempo_servico_id->tempos_servico])
    materiais(id, designacao, referencia, descricao)
    materiais_aplicados(id, material_id->materiais, quantidade)
    tempos_servico(id, data, mins, utilizador_id->utilizador);
//informacao para fazer a ligacao entre o material pedido e para que carro e para ser confirmado na ficha final
    materiais_pedidos(id, material_id->materiais, viatura_id->viaturas)
//calendario para marcar servico
    marcacoes(id, data, desc_avaria_cliente, cliente_id->cliente, viatura_id-> viaturas);
