# JEDI-API

Descrição

## Guia das Consulltas

Para aplicar um filtro no get use o nome das colunas da tabela.

| Símbolo | Tradução |
|:-------:|:--------:|
| &       | AND      |
|         |          |
| ()      | ()       |
| >       | >        |
| <       | <        |
| =       | =        |
| !       | !        |
| ""      | ""       |
| ''      | ''       |

## System User

### Autenticar

Recurso responsável por verificar se um usuário existe e está ativo.

[POST] https://memore-net.com/api/JEDI-API/system_user/autenticar

#### Body Entrada (vetor json)

```json
{
    "login": "userTeste",
    "password": "userTeste"
}
```

| Nome  | Descrição        | Tipo   |
|:-----:|:----------------:|:------:|
| login | Login do usuário | String |

#### Body Saída (vetor json)

```json
{
    "id": "0",
    "name": "userTeste",
    "login": "userTeste",
    "email": "userTeste@gmail.com",
    "frontpage_id": "41",
    "active": "Y"
}
```

| Nome         | Descrição                    | Tipo   |
|:------------:|:----------------------------:|:------:|
| id           | Identificador do usuário     | INT    |
| name         | Nome do usuário              | String |
| login        | Login do usuário             | String |
| email        | Email de cadastro do usuário | Strinh |
| frontpage_id |                              |        |
| active       | Status atual do login        | String |

## Pergunta2

### Sortear Perguntas

Recurso responsável por sortear de forma aleatória uma quantidade de perguntas definida, contando que tenha uma fala proposta.

[POST] https://memore-net.com/api/JEDI-API/pergunta2/sortearPerguntas

#### Body Entrada (json)

```json
{
    "quantidade": 3
}
```

| Nome       | Descrição                              | Tipo |
|:----------:|:--------------------------------------:|:----:|
| quantidade | Quantidade de perguntas a ser sorteada | INT  |

#### Body Saída (vetor json)

```json
[
    {
        "idtema": "17",
        "id": "1430",
        "pergunta": "Publicado em 15/10/2022. Musk volta atrás e diz que continuará financiando rede de internet Starlink na Ucrânia | Mundo | G1",
        "respcerta": "NÃO FAKE",
        "resp2": "FAKE",
        "resp3": null,
        "resp4": null,
        "caminhoimagem": "sem_imagem.png",
        "caract_proposta": null,
        "analise_proposta": "1. Ausência de Corroboração em Outras Fontes; porque a notícia foi publicada pelo G1, que é um veículo confiável e reconhecido, mostrando que a informação está confirmada por uma fonte séria;    2. Erros de Escrita e Formatação Amadora; a notícia tem texto bem escrito e organizado, sem erros bobos, indicando que passou por revisão editorial profissional;    3. Conteúdo Emocional, Parcial ou Extremado; a notícia apresenta os fatos de forma clara e objetiva, sem exageros ou opiniões fortes, o que ajuda a manter a informação confiável.",
        "analise_gpt": "1. Ausência de Corroboração em Outras Fontes; porque a notícia foi publicada pelo G1, que é um veículo confiável e reconhecido, mostrando que a informação está confirmada por uma fonte séria;  \r\n2. Erros de Escrita e Formatação Amadora; a notícia tem texto bem escrito e organizado, sem erros bobos, indicando que passou por revisão editorial profissional;  \r\n3. Conteúdo Emocional, Parcial ou Extremado; a notícia apresenta os fatos de forma clara e objetiva, sem exageros ou opiniões fortes, o que ajuda a manter a informação confiável.",
        "analise_gemini": null,
        "origem_analise": "1",
        "fala_gpt": "Olha só, essa notícia é daquelas que não exagera no título, vem de um site confiável, tem fontes claras e não tenta mexer com seu medo ou raiva, então dá pra confiar que não é fake.",
        "fala_gemini": null,
        "origem_fala": "1",
        "fala_proposta": "Olha só, essa notícia é daquelas que não exagera no título, vem de um site confiável, tem fontes claras e não tenta mexer com seu medo ou raiva, então dá pra confiar que não é fake.",
        "publica": "1"
    },
    {
        "idtema": "17",
        "id": "1432",
        "pergunta": "Portugal facilita vistos para nômades digitais | Turismo e Viagem | G1",
        "respcerta": "NÃO FAKE",
        "resp2": "FAKE",
        "resp3": null,
        "resp4": null,
        "caminhoimagem": "sem_imagem.png",
        "caract_proposta": null,
        "analise_proposta": null,
        "analise_gpt": "Título Sensacionalista ou Alarmista ou Chamativo; O título é direto e informativo, sem exageros ou promessas milagrosas, indicando que Portugal facilitou vistos para nômades digitais de forma clara e objetiva; Erros de Escrita e Formatação Amadora; A notícia publicada no G1 apresenta texto bem revisado, com boa gramática e formatação profissional, típica de veículos jornalísticos confiáveis; Ausência de Corroboração em Outras Fontes; A informação sobre Portugal facilitar vistos para nômades digitais é confirmada por vários veículos de imprensa reconhecidos, mostrando que não é uma notícia isolada ou sem respaldo.",
        "analise_gemini": null,
        "origem_analise": "1",
        "fala_gpt": "Olha só, essa notícia é de boa porque o título é direto, não tem erro bobo, cita fontes confiáveis, o site é conhecido, o texto é só informação sem drama, a notícia é atual, as imagens são reais, vários veículos publicaram, não te pedem pra sair compartilhando correndo e não tem aquelas teorias malucas de conspiração.",
        "fala_gemini": null,
        "origem_fala": "1",
        "fala_proposta": "Confia! Vai que é de boa!  É verdade porque o título é direto, não tem erro bobo, cita fontes confiáveis, o site é conhecido, o texto é só informação sem drama e vários veículos publicaram.",
        "publica": "1"
    },
    {
        "idtema": "17",
        "id": "1434",
        "pergunta": "Musk diz que SpaceX não pode financiar Starlink indefinidamente na Ucrânia | Ucrânia e Rússia | G1",
        "respcerta": "NÃO FAKE",
        "resp2": "FAKE",
        "resp3": null,
        "resp4": null,
        "caminhoimagem": "sem_imagem.png",
        "caract_proposta": null,
        "analise_proposta": "Ausência de Título Sensacionalista: o título é direto e informativo, sem exageros ou alarmismos; Erros de Escrita e Formatação Amadora ausentes: a notícia apresenta texto bem escrito e estruturado, típico de jornalismo profissional; Ausência de Teorias da Conspiração ou Promessas Exageradas: a notícia traz declaração real de Musk sem misturar especulações ou narrativas fantasiosas.",
        "analise_gpt": "Ausência de Título Sensacionalista: o título é direto e informativo, sem exageros ou alarmismos; Erros de Escrita e Formatação Amadora ausentes: a notícia apresenta texto bem escrito e estruturado, típico de jornalismo profissional; Ausência de Teorias da Conspiração ou Promessas Exageradas: a notícia traz declaração real de Musk sem misturar especulações ou narrativas fantasiosas.",
        "analise_gemini": null,
        "origem_analise": "1",
        "fala_gpt": "Olha só, essa notícia é confiável porque o título é direto e não exagera, o texto está bem escrito, cita fontes conhecidas como o G1 e não tenta te fazer sentir medo ou raiva só pra você compartilhar correndo.",
        "fala_gemini": null,
        "origem_fala": "1",
        "fala_proposta": "\"Papo reto\", essa notícia é confiável porque o título é direto e não exagera, o texto está bem escrito, cita fontes conhecidas como o G1 e não tenta te fazer sentir medo ou raiva só pra você compartilhar correndo.",
        "publica": "1"
    }
]
```

| Nome             | Descrição                                           | Tipo   |
|:----------------:|:---------------------------------------------------:|:------:|
| idTema           | Identificador do tema corespondente aquela pergunta | INT    |
| id               | Identificador da pergunta                           | INT    |
| pergunta         | Texto da pergunta                                   | String |
| respcerta        | Opção correta a se responder                        | String |
| resp2            | Segunda opção de resposta                           | String |
| resp3            | Terceira opção de resposta                          | String |
| resp4            | Quarta opção de resposta                            | String |
| caminhoimagem    | URL da imagem de apoio que acompanha a pergunta     | String |
| caract_proposta  |                                                     |        |
| analise_proposta | Análise escolhida para ser usada                    | String |
| analise_gpt      | Análise gerada pela IA chatGPT                      | String |
| analise_gamini   | Análise gerada pela IAgemini                        | String |
| origem_analise   |                                                     |        |
| fala_gpt         | Resposta sobre a pergunta gerada pela IA chatGPT    | String |
| fala_gemini      | Resposta sobre a pergunta gerada pela IA gemini     | String |
| origem_fala      |                                                     |        |
| fala_proposta    | Resposta escolhida para ser usada                   | String |
| publica          | Código que defini se está pública ou não            | INT    |

### Listar Pergunta

Recurso responsável por listar todas as perguntas registradas.

[GET] https://memore-net.com/api/JEDI-API/pergunta2/listarPergunta/{filtro}

#### Body Saída (vetor json):

```json
[
    {
        "idtema": "17",
        "id": "1430",
        "pergunta": "Publicado em 15/10/2022. Musk volta atrás e diz que continuará financiando rede de internet Starlink na Ucrânia | Mundo | G1",
        "respcerta": "NÃO FAKE",
        "resp2": "FAKE",
        "resp3": null,
        "resp4": null,
        "caminhoimagem": "sem_imagem.png",
        "caract_proposta": null,
        "analise_proposta": "1. Ausência de Corroboração em Outras Fontes; porque a notícia foi publicada pelo G1, que é um veículo confiável e reconhecido, mostrando que a informação está confirmada por uma fonte séria;    2. Erros de Escrita e Formatação Amadora; a notícia tem texto bem escrito e organizado, sem erros bobos, indicando que passou por revisão editorial profissional;    3. Conteúdo Emocional, Parcial ou Extremado; a notícia apresenta os fatos de forma clara e objetiva, sem exageros ou opiniões fortes, o que ajuda a manter a informação confiável.",
        "analise_gpt": "1. Ausência de Corroboração em Outras Fontes; porque a notícia foi publicada pelo G1, que é um veículo confiável e reconhecido, mostrando que a informação está confirmada por uma fonte séria;  \r\n2. Erros de Escrita e Formatação Amadora; a notícia tem texto bem escrito e organizado, sem erros bobos, indicando que passou por revisão editorial profissional;  \r\n3. Conteúdo Emocional, Parcial ou Extremado; a notícia apresenta os fatos de forma clara e objetiva, sem exageros ou opiniões fortes, o que ajuda a manter a informação confiável.",
        "analise_gemini": null,
        "origem_analise": "1",
        "fala_gpt": "Olha só, essa notícia é daquelas que não exagera no título, vem de um site confiável, tem fontes claras e não tenta mexer com seu medo ou raiva, então dá pra confiar que não é fake.",
        "fala_gemini": null,
        "origem_fala": "1",
        "fala_proposta": "Olha só, essa notícia é daquelas que não exagera no título, vem de um site confiável, tem fontes claras e não tenta mexer com seu medo ou raiva, então dá pra confiar que não é fake.",
        "publica": "1"
    }
]
```

| Nome             | Descrição                                           | Tipo   |
|:----------------:|:---------------------------------------------------:|:------:|
| idTema           | Identificador do tema corespondente aquela pergunta | INT    |
| id               | Identificador da pergunta                           | INT    |
| pergunta         | Texto da pergunta                                   | String |
| respcerta        | Opção correta a se responder                        | String |
| resp2            | Segunda opção de resposta                           | String |
| resp3            | Terceira opção de resposta                          | String |
| resp4            | Quarta opção de resposta                            | String |
| caminhoimagem    | URL da imagem de apoio que acompanha a pergunta     | String |
| caract_proposta  |                                                     |        |
| analise_proposta | Análise escolhida para ser usada                    | String |
| analise_gpt      | Análise gerada pela IA chatGPT                      | String |
| analise_gamini   | Análise gerada pela IAgemini                        | String |
| origem_analise   |                                                     |        |
| fala_gpt         | Resposta sobre a pergunta gerada pela IA chatGPT    | String |
| fala_gemini      | Resposta sobre a pergunta gerada pela IA gemini     | String |
| origem_fala      |                                                     |        |
| fala_proposta    | Resposta escolhida para ser usada                   | String |
| publica          | Código que defini se está pública ou não            | INT    |

## Partidas Perguntas

### Ranking

Recurso responsável por montar um ranking com os 10 jogadores com maior pontuação e retornar um resumo da partida atual do jogador

[POST] http://localhost/JEDI-API/partidasperguntas/ranking

#### Body Entrada (json)

```json
{
    "idPartida": 40,
    "jogador": "Fulano"
} 
```

| Nome      | Descrição                                    | Tipo |
|:---------:|:--------------------------------------------:|:----:|
| idPartida | Identificador da partida jogada pelo usuário | INT  |

#### Body Saída (vetor json)

```json
[
    {
        "idPartida": 8,
        "nome": "Usuario Teste",
        "jogador": "Júlia",
        "pontuacao": "94833.3",
        "percentualAcertos": "83.5294",
        "tempoGasto": "0",
        "totalPartidas": "21",
        "posicao": 1
    },
    {
        "idPartida": 44,
        "nome": "Joãozinho",
        "jogador": "João",
        "pontuacao": "93607.7",
        "percentualAcertos": "100.0000",
        "tempoGasto": "3671",
        "totalPartidas": "1",
        "posicao": 2
    },
    {
        "idPartida": 40,
        "nome": "Fulano",
        "jogador": "João",
        "pontuacao": "86414.3",
        "percentualAcertos": "100.0000",
        "tempoGasto": "482",
        "totalPartidas": "1",
        "posicao": 3
    },
    {
        "idPartida": 1,
        "nome": "João Silva",
        "jogador": "Thiago",
        "pontuacao": "66966.7",
        "percentualAcertos": "58.8235",
        "tempoGasto": "100",
        "totalPartidas": "19",
        "posicao": 4
    },
    {
        "idPartida": 5,
        "nome": "Usuario Teste",
        "jogador": "João",
        "pontuacao": "1000",
        "percentualAcertos": null,
        "tempoGasto": "0",
        "totalPartidas": "3",
        "posicao": 5
    },
    {
        "idPartida": 40,
        "nome": "Fulano",
        "jogador": "João",
        "pontuacao": "86414.3",
        "percentualAcertos": "100.0000",
        "tempoGasto": "482",
        "totalPartidas": "1",
        "posicao": 3
    }
]
```

| Nome              | Descrição                         | Tipo   |
|:-----------------:|:---------------------------------:|:------:|
| idPartida         | Identificador da partida          | INT    |
| nome              | Nome escolhido para a partida     | String |
| jogador           | Nome do avatar                    | String |
| pontuacao         | Pontuação do usuário na partida   | Float  |
| percentualAcertos | Percentual de perguntas acertadas | Float  |
| totalPartidas     | Quantidade de partidas jogadas    | INT    |
| tempoGasto        | Tempo gasto na parita             | Float  |
| autoAvaliacao     | Avaliação dada pelo usuário       | String |
| avaliacaoJogo     | Avaliação dada pelo jogo          | String |
| posicao           | Posição do usuário no ranking     | INT    |

### Salvar Partida

Recurso que salvar uma partida junto com a última pergunda respondida por ele retornando o id da partida salva. Caso o id seja -1 cria um novo registro e caso envie um id diferente de -1 atualiza o registro da partida

[POST] http://localhost/JEDI-API/partidasperguntas/salvarPartida

#### Body Entrada (json)

```json
{
  "id": 10,
  "jogadorEmail": "usuario@email.com",
  "dataHoraInicio": "2023-10-27T10:00:00Z",
  "nome": "João Silva",
  "idade": 20,
  "autoAvaliacao": "Avançado",
  "avatar": "Titia",
  "tempoGasto": 120,
  "jogadas": [
         {
          "jogadaId": 1,
          "noticiaId": 1428,
          "avaliacaoCorreta": true,
          "tempoResposta": 15,
          "posicaoAvatar": 1
        }

    ]

}
```

| Nome             | Descrição                                        | Tipo   |
|:----------------:|:------------------------------------------------:|:------:|
| idPartida        | Identificador da partida                         | INT    |
| jogadorEmail     | Email do usuário                                 | String |
| dataHoraInicio   | Data do inicio da partida                        | String |
| nome             | Nome escolhido para a partida                    | String |
| idade            | Idade do usuário                                 | INT    |
| autoAvaliacao    | Avaliação dada pelo usuário                      | String |
| avatar           | Nome do avatar                                   | String |
| tempoGasto       | Tempo gasto na parita                            | Float  |
| jogadaId         | Identificador da jogada                          | INT    |
| noticiaId        | Identificador da notícia                         | INT    |
| avaliacaoCorreta | Identificador se a resposta está certa ou errada | Bool   |
| tempoResposta    | Tempo gasto para respoder a pergunta             | Float  |
| posicaoAvatar    | Posição do jogador no tabuleiro                  | INT    |

#### Body Saída (vetor json)

```json
{
    "id": 10
}
```

| Nome | Descrição                | Tipo |
|:----:|:------------------------:|:----:|
| id   | Identificador da partida | INT  |

### Listar Partida

Recurso responsável por listar todas as partidas registradas.

[GET] http://localhost/JEDI-API/partidasperguntas/listarPartida/

#### Body Saída (vetor json)

```json
[
    {
        "dtJogo": "2023-10-27",
        "idPartida": "1",
        "login": "usuario@emai",
        "tema": "17",
        "jogador": "Thiago",
        "idade": "20",
        "pontuacao": "200",
        "qtdAcertos": "1",
        "qtdErros": "1",
        "tempoGasto": "100",
        "autoAvaliacao": "Avançado",
        "avaliacaoJogo": "Noob",
        "nome": "João Silva"
    },
    {
        "dtJogo": "2023-10-27",
        "idPartida": "2",
        "login": "usuario@emai",
        "tema": "17",
        "jogador": "Thiago",
        "idade": "20",
        "pontuacao": "33933.3",
        "qtdAcertos": "3",
        "qtdErros": "3",
        "tempoGasto": "120",
        "autoAvaliacao": "Avançado",
        "avaliacaoJogo": "Iniciante",
        "nome": "João Silva"
    }
    ...
]
```

| Nome          | Descrição                        | Tipo   |
|:-------------:|:--------------------------------:|:------:|
| dtJogo        | Data do inicio da partida        | String |
| idPartida     | Identificador da partida         | INT    |
| login         | Email do usuário                 | String |
| tema          | Identificador do tema            | INT    |
| jogador       | Nome do avatar                   | String |
| idade         | Idade do usuário                 | INT    |
| pontuacao     | Pontuação do usuário na partida  | Float  |
| qtdAcertos    | Quantidade de acertos na partida | INT    |
| qtdErros      | Quantidade de erros na partida   | INT    |
| tempoGasto    | Tempo gasto na parita            | Float  |
| autoAvaliacao | Avaliação dada pelo usuário      | String |
| avaliacaoJogo | Avaliação dada pelo jogo         | String |
| nome          | Nome escolhido para a partida    | String |

## Log Perguntas

### Listar Log Perguntas

Recurso responsável por listar todos os logs de perguntas .

[GET] https://memore-net.com/api/JEDI-API/logPerguntas/listarLogPergunta/{filtro}

#### Body Saída (vetor json)

```json
[
    {
        "dtJogo": "2026-03-12",
        "idPartida": "24",
        "usuario": "userTeste@gmail.com",
        "idade": "15",
        "tema": "17",
        "jogador": "Thiago",
        "numJogada": "1",
        "pergunta": "63",
        "respCerta": "FAKE",
        "respDada": "FAKE",
        "tempoGasto": "25",
        "posicao": "0"
    },
    {
        "dtJogo": "2026-03-12",
        "idPartida": "24",
        "usuario": "userTeste@gmail.com",
        "idade": "15",
        "tema": "17",
        "jogador": "Thiago",
        "numJogada": "2",
        "pergunta": "1430",
        "respCerta": "NÃO FAKE",
        "respDada": "FAKE",
        "tempoGasto": "118",
        "posicao": "3"
    },
    {
        "dtJogo": "2026-03-12",
        "idPartida": "24",
        "usuario": "userTeste@gmail.com",
        "idade": "15",
        "tema": "17",
        "jogador": "Thiago",
        "numJogada": "3",
        "pergunta": "26",
        "respCerta": "FAKE",
        "respDada": "FAKE",
        "tempoGasto": "294",
        "posicao": "3"
    },
    {
        "dtJogo": "2026-03-12",
        "idPartida": "24",
        "usuario": "userTeste@gmail.com",
        "idade": "15",
        "tema": "17",
        "jogador": "Thiago",
        "numJogada": "4",
        "pergunta": "26",
        "respCerta": "FAKE",
        "respDada": "FAKE",
        "tempoGasto": "216",
        "posicao": "8"
    },
    {
        "dtJogo": "2026-03-12",
        "idPartida": "24",
        "usuario": "userTeste@gmail.com",
        "idade": "15",
        "tema": "17",
        "jogador": "Thiago",
        "numJogada": "5",
        "pergunta": "60",
        "respCerta": "FAKE",
        "respDada": "FAKE",
        "tempoGasto": "156",
        "posicao": "21"
    },
    {
        "dtJogo": "2026-03-12",
        "idPartida": "24",
        "usuario": "userTeste@gmail.com",
        "idade": "15",
        "tema": "17",
        "jogador": "Thiago",
        "numJogada": "6",
        "pergunta": "1428",
        "respCerta": "NÃO FAKE",
        "respDada": "NÃO FAKE",
        "tempoGasto": "38",
        "posicao": "27"
    }
]
```

| Nome       | Descrição                                      | Tipo   |
|:----------:|:----------------------------------------------:|:------:|
| dtJogo     | Data de quando a partida foi iniciada          | String |
| idPartida  | Identificação da partida que o log pertence    | INT    |
| usuario    | Email do usuário                               | String |
| idade      | Idade declarada pelo usuário                   | INT    |
| tema       | Identificação do tema do jogo escolhido        | INT    |
| jogador    | Nome do avatar escolhido                       | String |
| numJogada  | Número da jogada feita                         | INT    |
| pergunta   | Identificador da pergunta feita                | INT    |
| respCerta  | Resposta certa da pergunta feita               | String |
| respDada   | Respota dada pelo usuário                      | String |
| tempoGasto | Tempo gasto para responder a pergunta          | Float  |
| posicao    | Posição do jogador quando respondeu a pergunta | INT    |
