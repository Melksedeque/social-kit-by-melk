# Changelog

## [1.0.0] - 2026-10-08

### Added
- Geração automática de rótulo, título do card, texto do card e legenda do X ao salvar o post, usando regras determinísticas configuráveis via filtros `skbm_*`.
- Painel "Social Kit" no editor (Gutenberg) com contador de caracteres ponderado (URL = 23, CJK/emoji = 2), botões de copiar e "Abrir no X".
- Trava manual (`_skbm_locked`) para impedir que a regeneração automática sobrescreva edições feitas à mão.
- Integração opcional com o URL Shortener by Melk (via `urlshbym_get_short_url_for_post()`), com fallback para o permalink quando o outro plugin não está ativo.
- Tela de configurações (`Configurações > Social Kit`) para escolher os tipos de conteúdo habilitados, com seção "Outros plugins by Melk".
- Reaproveitamento da palavra-chave principal do Yoast SEO ou Rank Math (quando um deles está ativo) na primeira hashtag, via filtro `skbm_primary_keyword`; fallback para a primeira tag do post quando nenhum plugin de SEO está ativo. Aviso discreto na tela de configurações sugerindo um plugin de SEO, sem exigir nenhum.

### Corrigido
- A legenda e o texto do card preservam as quebras de linha (antes `sanitize_text_field` as transformava em espaço ao salvar).
- A legenda é regerada quando a URL muda (ex.: rascunho que ganha link curto ou permalink ao publicar); antes ficava com o `?p=ID` do rascunho. Rascunhos automáticos (`auto-draft`) não geram mais conteúdo.
- O contador de caracteres segue os pesos oficiais do X (ex.: `…` conta 2, `❤️` e sequências de emoji contam 2) em PHP e JS.
- Metas registrados só nos tipos de conteúdo habilitados, com permissão `edit_post` por post; `source_hash` e `version` não são mais expostos na REST.
- O botão "Regenerar" não grava mais nada no banco (só devolve o resultado para o editor) e valida os parâmetros e o tipo de conteúdo.
- Painel: usa `wp.editor` (fallback `wp.editPost`), envia só o campo alterado, mostra erro e estado de carregamento, indica e permite destravar a edição manual, e o "Copiar tudo" não repete as hashtags.

### Modificado
- Requer WordPress 6.2 ou superior; testado até o WordPress 7.1.
- O card "Outros plugins by Melk" aponta para a página do URL Shortener no WordPress.org.

### Adicionado
- Filtro `skbm_resolve_url` para trocar a URL usada na legenda.
- Testes automatizados leves em `tests/` (`php tests/run.php`), sem Composer.
