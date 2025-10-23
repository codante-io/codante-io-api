## Escopo

Implementar e ajustar o fluxo de pagamento via Pagarme (Core v5 Orders + Checkout) para o curso ao vivo “Codando com IA”, sem liberar área de acesso ou enviar e-mails por enquanto, garantindo associação/criação de usuário e notificações no Discord.

## Requisitos Confirmados

- **Preço:** R$ 588,00 (58800 centavos), parcelamento em até 12x sem juros.
- **Métodos de pagamento:** cartão de crédito, boleto e pix.
- **Acesso/e-mails:** nenhum acesso adicional nem e-mails de confirmação neste momento.
- **Usuário:** reutilizar usuário existente pelo e-mail ou criar um novo se não existir.
- **Webhooks:** reutilizar o fluxo já existente, com tratamento específico para o produto do curso.
- **URLs e metadados:** `success_url` = `/curso-ao-vivo/codando-com-ia/sucesso`; `product_slug` = `curso-ao-vivo-codando-com-ia-v1`.

## Estado Atual

- **POST `/api/pagarme/codando-com-ia/checkout`** já cria a Order + Checkout, retornando `payment_url`.
- **GET `/api/pagarme/codando-com-ia/orders/{orderId}`** consulta a Order no Core v5, validando `product_slug`.
- **POST `/api/pagarme/notification`** processa webhooks; atualmente o fluxo é focado em Subscription e apenas registra quando não encontra subscription para este produto.

## Plano de Implementação

### 1. Plano (`codando-com-ia-v1`)

- Garantir via seed/migração idempotente a existência do plano com:
  - `slug`: `codando-com-ia-v1`
  - `name`: `Codando com IA (Ao Vivo) v1`
  - `price_in_cents`: `58800`
  - `duration_in_months`: `0`
  - `details`: JSON opcional

### 2. Checkout (`POST /api/pagarme/codando-com-ia/checkout`)

- Sanitizar telefone (já implementado) e aceitar ausência sem falhar.
- Localizar usuário pelo e-mail; se não existir, criar com nome, e-mail, telefone (se válido) e senha aleatória hasheada; atualizar dados faltantes quando reaproveitar usuários.
- Incluir `customer.metadata.user_id` no payload enviado ao Pagarme.
- Manter preço, métodos aceitos, parcelas sem juros, `success_url` e metadados existentes.
- Após sucesso na criação da Order:
  - Criar `Subscription` vinculada ao plano `codando-com-ia-v1` com `status = pending`, `acquisition_type = purchase`, `provider_id = order.id`, `price_paid_in_cents = order.amount` e `payment_method = null` (até atualização via webhook).
- Retornar `checkoutLink`, `pagarmeOrderID`, `amount`, `status` (sem autenticar/logar o usuário nem disparar e-mails).

### 3. Status (`GET /api/pagarme/codando-com-ia/orders/{orderId}`)

- Manter lógica atual de validação do `product_slug` e retorno filtrado dos dados da Order.

### 4. Webhooks (`POST /api/pagarme/notification`)

- Para eventos `order.*` onde `metadata.product_slug === curso-ao-vivo-codando-com-ia-v1` ou Subscription vinculada ao plano `codando-com-ia-v1`:
  - `findOrCreate` do `User` (atualizando nome/telefone ausentes).
  - Localizar ou criar a `Subscription` pendente vinculada ao plano.
  - Atualizar `status` e campos `payment_method`, `boleto_url`, `boleto_barcode` / `qr_code` diretamente (não usar `changeStatus` para evitar `upgradeUserToPro`).
  - Enviar mensagem ao Discord no canal `notificacoes-compras` com resumo do evento (comprador, método, valor, `orderId`, status e contexto).
  - Não liberar acesso, não enviar e-mails.
  - Garantir idempotência atualizando apenas dados necessários.

### 5. Segurança e Resiliência

- Manter uso de Basic Auth para chamadas à API Pagarme.
- Tolerar telefones inválidos/vazios sem lançar exceções.
- Preservar e detalhar logs de erro no checkout.

### 6. Testes Automatizados

- **Checkout:** garantir criação/associação do usuário e subscription pendente, além do retorno do `payment_url`.
- **Status:** confirmar 200 quando o `product_slug` bate e 404 caso contrário.
- **Webhooks:** simular `order.created`, `order.closed`, `order.paid`, `order.canceled/failed` para validar atualizações da subscription, criação/atualização de usuário e envio de notificações ao Discord (mockado), assegurando que não há promoção a PRO.

### 7. Pós-Implementação

- Rodar migration/seed para criar o plano.
- Executar `php artisan test` para validar o backend.
- Certificar que o webhook do Pagarme aponta para `/api/pagarme/notification` e que `PAGARME_API_KEY` correta está configurada no `.env` de cada ambiente.
