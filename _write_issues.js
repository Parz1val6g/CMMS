const fs = require('fs');
const parts = [];
parts.push(`# Issues — Campos Estendidos de Equipamentos\n`);
parts.push(`\n> Gerado a partir de: docs/prds/PRD_EQUIPMENT_EXTENDED_FIELDS.md\n`);
parts.push(`\n---\n`);
parts.push(`\n## ISSUE-001: Migrations — equipment_types + counting_types (2 novas tabelas)`);
parts.push(`\n\n**Labels:** backend, database, migration`);
parts.push(`\n**Milestone:** M1`);
parts.push(`\n**Estimativa:** 30min`);
parts.push(`\n**Dependencias:** Nenhuma`);
parts.push(`\n\n### Descricao\n`);
parts.push(`\nCriar as duas novas tabelas. Usar DB::statement() raw SQL para constraints CHECK (Blueprint nao suporta).`);
parts.push(`\n\n### Tasks\n`);
parts.push(`\n- [ ] Criar equipment_types: id (uuid PK), name (varchar 100), category (varchar 20) com CHECK via DB::statement(), active (bool), timestamps, softDeletes`);
parts.push(`\n- [ ] Criar counting_types: id (uuid PK), name (varchar 100), value (varchar 50), active (bool), timestamps, softDeletes`);
parts.push(`\n\n### Criterios\n`);
parts.push(`\n- [ ] php artisan migrate sem erros`);
parts.push(`\n- [ ] Tabelas existem`);
parts.push(`\n- [ ] CHECK constraint rejeita valores invalidos