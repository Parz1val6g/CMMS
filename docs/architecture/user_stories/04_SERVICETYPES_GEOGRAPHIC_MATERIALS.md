# User Stories: Service Types, Geographic & Materials

---

## 🏷️ US-040: Criar Tipo de Serviço

**Como** admin,  
**Eu quero** definir um novo tipo de serviço,  
**Para que** eu possa categorizar trabalhos.

### Critérios de Aceitação
- ✅ POST /service-types com: name, description
- ✅ Name: unique, max 100 chars
- ✅ Description: max 250 chars
- ✅ Status: active (default)
- ✅ Criação registada na auditoria

### Exemplos
- "Reparação de Estradas"
- "Limpeza de Esgotos"
- "Reparação Eletricidade"

---

## 🏷️ US-041: Listar Tipos de Serviço

**Como** manager,  
**Eu quero** ver todos os tipos de serviço,  
**Para que** eu possa selecioná-los ao criar ordens.

### Critérios de Aceitação
- ✅ GET /service-types com paginação
- ✅ Filtros: status, search (name/description)
- ✅ Campos: id, name, description, status, orders_count
- ✅ Apenas active por default (opção show archived)

---

## 🏷️ US-042: Editar Tipo de Serviço

**Como** admin,  
**Eu quero** atualizar informações de um tipo de serviço,  
**Para que** dados permaneçam corretos.

### Critérios de Aceitação
- ✅ PUT /service-types/{id} com: name, description, status
- ✅ Name deve ser único
- ✅ Auditoria: mudanças registadas
- ✅ Apenas admin

---

## 🏷️ US-043: Deletar Tipo de Serviço

**Como** admin,  
**Eu quero** remover um tipo de serviço não utilizado,  
**Para que** sistema limpo.

### Critérios de Aceitação
- ✅ DELETE /service-types/{id}
- ✅ Verificar: se em uso (service_orders) → erro
- ✅ Soft delete se sem uso
- ✅ Apenas admin

---

## 🌍 US-044: Listar Distritos

**Como** qualquer utilizador,  
**Eu quero** ver lista de distritos,  
**Para que** eu possa selecionar localização.

### Critérios de Aceitação
- ✅ GET /districts com paginação
- ✅ Filtros: search (name)
- ✅ Campos: id, name, municipality_count

---

## 🌍 US-045: Listar Municípios por Distrito

**Como** utilizador,  
**Eu quero** filtrar municípios por distrito,  
**Para que** eu possa navegar hierarquia geográfica.

### Critérios de Aceitação
- ✅ GET /municipalities?district_id={id} com paginação
- ✅ Campos: id, name, parish_count
- ✅ Validação: district_id deve existir

---

## 🌍 US-046: Listar Freguesias por Município

**Como** utilizador,  
**Eu quero** listar freguesias de um município,  
**Para que** eu possa selecionar localização.

### Critérios de Aceitação
- ✅ GET /parishes?municipality_id={id} com paginação
- ✅ Campos: id, name, location_count
- ✅ Validação: municipality_id válido

---

## 📍 US-047: Criar Localização

**Como** manager,  
**Eu quero** registar uma nova localização,  
**Para que** eu possa associar a service orders.

### Critérios de Aceitação
- ✅ POST /locations com:
  - parish_id (required)
  - street_address (required, max 100)
  - postal_code (required, format PT, 8 chars)
  - landmark (max 100, optional)
  - latitude/longitude (optional, decimal 10,8)
- ✅ Geolocalização: se lat/long vazios, tentar API (Google Maps)
- ✅ Auditoria: criador, timestamp

### Validações
- postal_code: formato PT (XXXX-XXX)
- parish_id: validar hierarquia (existe na DB)
- coordinates: se fornecidos, validar range (-90 a 90 lat, -180 a 180 long)

---

## 📍 US-048: Listar Localizações

**Como** manager,  
**Eu quero** procurar localizações,  
**Para que** eu possa selecioná-las em service orders.

### Critérios de Aceitação
- ✅ GET /locations com paginação
- ✅ Filtros:
  - search (street_address, postal_code, landmark)
  - parish_id, municipality_id, district_id (hierarchical)
  - near (lat, long, radius em km)
- ✅ Campos: id, address, postal_code, landmark, parish, orders_count

### Notas
- Usar índices de geolocalização se possível
- Near filter: usar ST_Distance se MySQL suporta

---

## 📍 US-049: Visualizar Detalhes de Localização

**Como** manager,  
**Eu quero** ver informações completas de uma localização,  
**Para que** eu possa revisar detalhes.

### Critérios de Aceitação
- ✅ GET /locations/{id} retorna:
  - Todos dados, coordenadas, mapa (URL Google Maps)
  - Service orders associadas (últimas 10)
  - Clientes que têm ordens nesse local
  - Histórico de trabalho (últimos 20)

---

## 📍 US-050: Editar Localização

**Como** manager ou admin,  
**Eu quero** atualizar dados de uma localização,  
**Para que** informações fiquem atualizadas.

### Critérios de Aceitação
- ✅ PUT /locations/{id} com: street_address, postal_code, landmark, latitude, longitude
- ✅ Postal code: validação unique (per parish)
- ✅ Auditoria: mudanças registadas

---

## ⚙️ US-051: Criar Unidade de Medida

**Como** admin,  
**Eu quero** definir uma unidade de medida,  
**Para que** eu possa quantificar materiais.

### Critérios de Aceitação
- ✅ POST /units com: name, abbreviation
- ✅ Name: max 50 chars
- ✅ Abbreviation: unique, max 10 chars
- ✅ Auditoria: criador

### Exemplos
- kg, liters, boxes, meters, hours, pieces

---

## ⚙️ US-052: Listar Unidades de Medida

**Como** qualquer utilizador,  
**Eu quero** ver todas unidades disponíveis,  
**Para que** eu possa usar em materiais.

### Critérios de Aceitação
- ✅ GET /units com paginação
- ✅ Filtros: search (name, abbreviation)
- ✅ Campos: id, name, abbreviation, materials_count

---

## 📦 US-053: Criar Material

**Como** admin,  
**Eu quero** registar um novo material no inventário,  
**Para que** eu possa rastrear stock.

### Critérios de Aceitação
- ✅ POST /materials com:
  - name (required, unique, max 100)
  - unit_id (required, foreign key)
  - stock_quantity (decimal, default 0)
- ✅ Stock: sempre >= 0 (check constraint)
- ✅ Auditoria: criador, timestamp

### Notas
- Stock em unidades de medida (ex: 100 kg)
- Initial stock pode ser 0 (atualizar via adjust-stock)

---

## 📦 US-054: Listar Materiais

**Como** supervisor ou manager,  
**Eu quero** ver inventário de materiais,  
**Para que** eu possa verificar stock disponível.

### Critérios de Aceitação
- ✅ GET /materials com paginação
- ✅ Filtros: search (name), unit_id, low_stock (< threshold)
- ✅ Campos: id, name, unit, stock_quantity, status
- ✅ Ordenação: por name ou stock ascending
- ✅ Low stock: indicador visual (warning se < 10%)

---

## 📦 US-055: Visualizar Detalhes de Material

**Como** supervisor,  
**Eu quero** ver histórico completo de um material,  
**Para que** eu possa auditar uso.

### Critérios de Aceitação
- ✅ GET /materials/{id} retorna:
  - Dados: name, unit, stock_quantity
  - Usage history (últimos 30 work_logs usando este material)
  - Total utilizado (sum by month)
  - Preço unitário médio
  - Planned vs actual vs stock

---

## 📦 US-056: Ajustar Stock de Material

**Como** admin,  
**Eu quero** adicionar ou remover material do stock,  
**Para que** eu possa corrigir contagem e recebimentos.

### Critérios de Aceitação
- ✅ POST /materials/{id}/adjust-stock com:
  - quantity (signed: +100 ou -50)
  - reason (obrigatório: "received", "adjustment", "loss", etc.)
  - reference (optional: purchase order, note)
- ✅ Auditoria: quem ajustou, quando, razão
- ✅ Validação: resultado não negativo (rejeitar se resultado < 0)

### Exemplo
```
POST /materials/mat-001/adjust-stock
{
  "quantity": 50,
  "reason": "received",
  "reference": "PO-12345"
}
```

---

## 📦 US-057: Editar Material

**Como** admin,  
**Eu quero** atualizar informações de um material,  
**Para que** dados fiquem corretos.

### Critérios de Aceitação
- ✅ PUT /materials/{id} com: name, unit_id
- ✅ Name: unique (exceto self)
- ✅ Stock_quantity: NÃO editável diretamente (apenas via adjust-stock)
- ✅ Auditoria: mudanças

---

## 📦 US-058: Deletar Material

**Como** admin,  
**Eu quero** remover material não utilizado,  
**Para que** sistema limpo.

### Critérios de Aceitação
- ✅ DELETE /materials/{id} (soft delete)
- ✅ Verificar: se em uso em mini_tasks/work_logs → erro (não permitir)
- ✅ Apenas admin

---

## 📊 US-059: Relatório de Stock Low

**Como** manager,  
**Eu quero** receber alertas de materiais com stock baixo,  
**Para que** eu possa reabastecer a tempo.

### Critérios de Aceitação
- ✅ GET /materials?low_stock=true retorna materiais com stock < 10% ou < threshold customizável
- ✅ Notificação: email diário de low stock (opcional, via settings)
- ✅ Incluir: nome, stock atual, mínimo recomendado

### Notas
- Threshold: admin pode customizar em app_settings
- Low stock: automático quando criado material

---

## 📊 US-060: Relatório de Uso de Materiais

**Como** manager,  
**Eu quero** ver relatório de materiais usados por período,  
**Para que** eu possa analisar consumo.

### Critérios de Aceitação
- ✅ GET /reports/materials-usage?date_from=&date_to= retorna:
  - Por material: quantidade utilizada, custo, work_logs associados
  - Por service_order: total materiais usados
  - Por sector: comparação consumo
- ✅ Export: CSV, PDF (opcional)
- ✅ Filters: material_id, sector_id, date_range

---
