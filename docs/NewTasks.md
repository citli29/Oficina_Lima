Client Picker [ ]
Schedule Calendar [ ]
SAP Table [ ] -> Product Picker [x]
SUT Table [ ] -> User Picker [ ]
Car Picker [ ] -> Models [x] , Makes [x]
Service Table ->  Client, Car, SAP, SUT

Marcacoes:
    Atribuir Utilizador  (array)
    Atribuir Material selecionado
    
Servicos:
    Implementar tipos de servico:
        Mecanica
        Servico Laboratiorio
    Implementar sub-tipo de servico
        Injetores
        Injetores-Turbo
        Turbo
        Bomba
        Bomba-Injetores
        Bomba-Injetores-Turbo
        Bomba-Turbo
    Atribuir a uma marcacao (tipo picker)
    Ao abrir uma ficha, se tiver alguma ficha aberta, alertar
    Ao abrir uma ficha sem marcacao, se tiver alguma marcacao para o mesmo carro, alertar
    Perguntar estado de servico (cor)
    Deixar criar servico sem cliente, nao permitir entregar o carro sem ficha de cliente  (em vez de not null, fazer)
    No picker, default dia de hoje
    Adicionar Contacto e Nome
    SUT:
        Adicionar Notas

    


