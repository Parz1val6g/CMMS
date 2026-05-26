# Use Case 1 — Atendente

**Actor:** Ana Lima  
**Email:** `ana.lima@cm-mangualde.pt`  
**Password:** `password123`  
**Role:** `attendant`

---

## Responsabilidades neste use case

Recebe a chamada do cidadão, regista a Ordem de Serviço no sistema e entrega-a ao Gestor. Pode editar ou apagar a SO enquanto Pendente. Não intervém depois da activação.

---

## Permissões relevantes

| Recurso | Acções |
|---|---|
| Service Orders | view (suas), create, update (Pendente), delete (Pendente) |
| Clients | view |
| Locations | view |
| Service Types | view |
| Sectors | view |
| Users | view |
| Attachments | view, create |

Não vê Tarefas, Mini-Tarefas nem Work Logs.

---

## Passo a Passo

### 1. Login

1. Abre `http://localhost:8000`
2. Introduz o email e a password
3. Redireccionado directamente para o Dashboard de Atendente (role único — sem selector)

---

### 2. Registar a Ordem de Serviço (a partir da chamada)

1. Sidebar → **Gestão de Trabalho** → **Ordens de Serviço**
2. Clica **"+ Nova Ordem de Serviço"**
3. Preenche o formulário:

   **Secção Principal**

   | Campo | Descrição | Obrigatório |
   |---|---|---|
   | Gestor responsável | Gestor que irá activar e gerir a SO | Sim |
   | Data de Execução | Data prevista para a intervenção | Sim |
   | Descrição | Problema reportado na chamada | Não |
   | Cliente | Cliente que ligou, se estiver registado | Não |
   | Tipo de Serviço | Categoria do trabalho | Não |
   | Prioridade | Baixa / Normal / Alta / Urgente | Não |

   > O Atendente **não** selecciona sectores. Essa decisão é do Gestor.

   **Secção Localização**

   | Campo | Exemplo |
   |---|---|
   | Freguesia | Seleccionar da lista |
   | Morada | "Rua de Santo António, 12" |
   | Ponto de Referência | "Em frente ao Café Central" |
   | Código Postal | "3530-001" |

   **Secção Mapa** — pin opcional no mapa para coordenadas exactas

   **Secção Foto** — upload opcional (JPEG/PNG, máx 5 MB)

4. Clica **Guardar**
5. SO criada com status **PENDING** e referência automática (ex: `OS-2026-0001`)
6. A SO aparece na listagem do Atendente e fica visível para o Gestor seleccionado

---

### 3. Após criar a SO

O Atendente pode, enquanto a SO estiver **PENDING**:

- **Editar** — corrigir dados introduzidos durante a chamada
- **Apagar** — se a chamada foi engano ou duplicada
- **Adicionar anexos** — fotos ou documentos fornecidos pelo cidadão

Quando o Gestor activar a SO, o Atendente deixa de poder editar. A SO fica visível na sua listagem com o estado actualizado, mas sem acesso às Tarefas ou Mini-Tarefas.

---

## Resumo do Fluxo

```
Cidadão liga
  → Atendente regista SO (PENDING)
      → [Gestor recebe e activa → trabalho cascata]
  → Atendente pode consultar estado das suas SOs
```
