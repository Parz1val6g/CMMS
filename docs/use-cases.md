ordens de serviço primeiro

caso 1: uma pessoa liga para nos a reportar um problema na cidade para resolver
    pessoa atende e regista esse problema na ordem de serviço, com a data de inicio proposta, com estado pendente
    quando a ordem de serviço chega á data emite um alerta
    quando a pessoa responsável pela mesma "ativar" a ordem de serviço vão ser criadas as tarefas para cada setor
    cada pessoa responsável por cada tarefa vai dividir a mesma por várias mini-tarefas e cada uma vai ter que ter trabalhador/es e/ou equipa/s, materiais, equipamentos, data de inicio e fim previstas
    as pessoas responsáveis pelas mini-tarefas(trabalhadores/equipas) vão criar os worklogs com o material/equipamento usado para cada worklog com o timestamp de inicio e quando terminar do fim
    quando as mini-tarefas forem todas concluidas a tarefa será marcada como "a espera de aprovação ou pendente de aprovação, ou uma coisa assim", a pessoa responsável pela tarefa vai rever tudo e dar a tarefa como concluída
    ao fim de todos os setores darem as tarefas relativas a uma ordem de serviço como concluidas a pessoa responsável pela ordem de serviço irá rever tudo e marcar a ordem de serviço como concluída


caso 2: uma entidade precisa de alugar equipamento/s
    essa entidade acede ao nosso site
    preenche o formulário com os equipamento/s que precisa de dia x a dia y e se é ou n preciso operador para o mesmo(para cada equipamento é necessário definir uma data de inicio e fim e se é ou n necessário um operador), com a localização, uma descrição e submete
    a pessoa responsável pelos empréstimos cria uma ordem de serviço com os dados que tem atualmente incluindo as datas de inicio e fim previstas, atribuindo a mesma a uma pessoa, ficando a mesma pendente, com o tipo de emprestimo e com o id do emprestimo.
    formulário para o manager dos emprestimos criar ordem de serviço:
        mesmos campos do formulario da entidade que serão preenchidos automaticamente pelos dados do ticket
        campo para selecionar a pessoa que vai ficar responsável para ordem de serviço
    o gestor daquela ordem de serviço, analiza que equipamentos são necessários e a cada equipamento atribui uma secção de acordo com o chefe da mesma, criando assim uma task por equipamento.
    quando esse equipamento for levantado pela entidade ou entregue á entidade é criada uma mini tarefa com o equipamento e a pessoa que o entregou e por consequência um work-log que regista a/s pessoa/s que fizeram a entrega e com os timestamps, se for entrega ao local é registado o timestamp de quando sai da nossa instituição e depois quando o entrega. quando formos levantar o equipamento o mesmo processo(cria-se uma mini-task, e dentro dessa mini-task se for para levantar vai ser registado o timestamp desde que ele sai do nosso local até á entidade), se for para a entidade levantar no nosso estabelecimento e entregar ao nosso estabelecimento, é feito o registo na mesma com work-logs e mini-tasks

caso 3: nós precisamos de x equipamento/s para evento
    uma pessoa interna cria um ticket a dizer que é necessário x equipamento/s de dia x a dia y e se é ou n preciso operador para o mesmo(para cada equipamento é necessário definir uma data de inicio e fim e se é ou n necessário um operador)
    esse ticket é acedido pelo ticket manager, o mesmo cria uma ordem de serviço com os dados que tem atualmente incluindo as datas de inicio e fim previstas, atribuindo a mesma a uma pessoa e a ordem de serviço fica pendente, ainda com o tipo de "ticket" e ainda com o id do ticket
    o gestor daquela ordem de serviço, analiza que equipamentos são necessários e a cada equipamento atribui uma secção de acordo com o chefe da mesma, criando assim uma task por equipamento.
    quando esse equipamento for levantado pela entidade ou entregue á entidade é criada uma mini tarefa com o equipamento e a pessoa que o entregou e por consequência um work-log que regista a/s pessoa/s que fizeram a entrega e com os timestamps, se for entrega ao local é registado o timestamp de quando sai da nossa instituição e depois quando o entrega. quando formos levantar o equipamento o mesmo processo(cria-se uma mini-task, e dentro dessa mini-task se for para levantar vai ser registado o timestamp desde que ele sai do nosso local até á entidade), se for para a entidade levantar no nosso estabelecimento e entregar ao nosso estabelecimento, é feito o registo na mesma com work-logs e mini-tasks
    

caso 4: manutenção de equipamento
    todos os equipamentos têm a data da ultima manutenção, têm um periodo de manutenção, e quando chegar ao momento da manutenção irá ser emitido um aviso de que a manutenção tem que ser efetuada
    quando for para efetuar a manutenção irá ser criada uma manutenção e uma ordem de serviço que irá ter o tipo de ordem manutenção e o id da mesma. Depois a ordem de serviço irá funcionar da mesma forma que os emprestimos onde será criada depois uma tarefa que será atribuida ao departamento correto para ser feita a manutenção e serão criadas mini-tarefas com a data prevista de inicio e fim da manutenção. depois serão criados os worklogs que regista o timestamp exato do inicio e fim da manutenção e os materiais usados