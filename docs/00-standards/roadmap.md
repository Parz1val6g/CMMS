ticket - descrição, cliente, tipo de serviço, urgencia, localização, ao clicar no mapa buscar freguesia etc

ordem de serviço sede - ficar ao pe do cliente, ao selecionar morada do cliente preeche campos de localização

emprestimo separar num separador a parte~

mini-tarefa - estimativa de materiais/equipamentos, data inicio, data de fim previsto, selecionar primeiro equipa, e selecionar automaticamente trabalhadores de equipa

equipamento - tipo de equipamento, se viatura, data de inspeção(reminders), ultima data de revisão, periodicidade, tipo de contagem de revisão, n de série/matricula, referencia interna manual, ano de fabrico, anexos(documentação, livrete, certificados)

equipas - head

interface de pedido de emprestimo com auth
emprestimo - ticket entidade data inicio/fim, equipamento, operador


equipamentos/trabalhador/equipa - custo/hora

entidade / cliente - ordem de serviço

empréstimo apenas a entidade

1 - EQUIPMENT'S/WORKER'S/TEAM'S TABLE UPDATE
atualizar todo o sistema para adicionar uma coluna das tabelas dos equipments workers e teams para guardar o custo por hora daquele equipamento/equipa/trabalhador.

2 - UPDATE EQUIPMENT'S
na tabela/sistema/CRUD de equipamentos quero adicionar um campo que seja tipo de equipamentos(se é um veiculo pesado, se é um palco, se é um veiculo leve, etc) not null, adiconar também data de inspeção(para o caso de ser uma viatura), ultima data de revisão e matricula se for veiculo, periodicidade de revisão, tipo de contagem(se a revisão é feita a cada km's, se é a cada x dias, anos, horas de uso, e mais tipos que possam existir), n de série se não for veiculo, uma referência interna(um valor interno que a própria empresa tem e q a pessoa que faz a gestão dos equipamentos insere manualmente), ano de fabrico do equipamento e os anexos(onde é possivel anexar manuais, documentação, livrete do veiculo, certificados, etc)

3 - TEAM TABLE UPDATE
adicionar um responsável por cada equipa

4 - MOVE LOAN LOGIC
no formulário de criação de ordem de serviço está atualmente um toggle para alternar entre uma ordem de serviço normal e uma ordem de serviço tipo loan, no entanto eu quero passar toda a lógica dos loans para um CRUD separado, conseguindo assim uma maior organização.

5 - UPDATE SERVICE-ORDER CREATION FORM
no formulário de criação das ordens de serviço, quando o cliente é selecionado aparece um select para selecionarmos o local onde vai ser executada a ordem de serviço. eu quero passar esse select para debaixo do input do cliente, e quando for selecionada uma localização, todos os inputs em baixo relativos á localização devem ser automaticamente preenchidos com os dados dessa localização. se o user mudar algum campo da localização ao fim da localização do cliente ser selecionada, o select da localização é alterado para vazio e os dados são submetidos dessa forma. se o user não alterar nenhum dado da parte da localização, quando o form é submetido os campos da localização não são enviados apenas é enviado o select da localização do cliente, assim não é criada uma nova localização.

6 - CREATE SERVICE ORDER TICKET SYSTEM
pessoa liga para empresa e faz um pedido para fazer algo(compor buraco na estrada, ou outra coisa)e a pessoa com o cargo ticket_manager cria ticket de ordem de serviço com toda a informação relativa ao pedido da pessoa. esse ticket vai ser criado e o gestor das service orders vai criar uma ordem de serviço relativa a esse ticket. um ticket de ordem de serviço tem que ter descrição, cliente, tipo de serviço, urgencia, localização. preciso de criar uma tabela e todo o backend/frontend para o CRUD de um ticket.

7 - CREATE LOAN TICKET SYSTEM
entidade(novo role que camaras municipais, juntas de freguesia, etc irão receber) acede ao site, faz login e tem acesso a uma página com um formulário de empréstimo. Depois faz o pedido do empréstimo inserindo os seguintes dados: entidade(vai ser auto-preenchido com os dados da entidade logada), equipamento, toggle se tem ou não operador(se é necessário requesitar também operador/es para trabalhar com o equipamento), data de inicio/levantamento por equipamento, data de fim/entrega por equipamento). nova regra também, apenas entidades podem fazer empréstimos.

8 - UPDATE MINI-TASKS
preciso de adicionar na tabela/sistema/CRUD das mini-tasks, um campo para adicionar uma estimativa de materiais e equipamentos a serem usados, uma data de inicio e uma data de fim prevista, no formulário de criação ao selecionar uma equipa o estado dos trabalhadores ligados a essa equipa é alterado automaticamente depois ou é necessário adicionar também os trabalhadores? Se for necessário adicionar os trabalhadores, quando a equipa é selecionada, no select dos workers os workers relacionados á/s equipa/s selecionada/s devem ficar também selecionados e não podem ser descelecionados manualmente, apenas é possivel removê-los se descelecionarmos a equipa.
