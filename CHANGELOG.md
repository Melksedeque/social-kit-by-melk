# Changelog

## [1.0.0] - 2026-10-08

### Added
- Geração automática de rótulo, título do card, texto do card e legenda do X ao salvar o post, usando regras determinísticas configuráveis via filtros `skbm_*`.
- Painel "Social Kit" no editor (Gutenberg) com contador de caracteres ponderado (URL = 23, CJK/emoji = 2), botões de copiar e "Abrir no X".
- Trava manual (`_skbm_locked`) para impedir que a regeneração automática sobrescreva edições feitas à mão.
- Integração opcional com o URL Shortener by Melk (via `urlshbym_get_short_url_for_post()`), com fallback para o permalink quando o outro plugin não está ativo.
- Tela de configurações (`Configurações > Social Kit`) para escolher os tipos de conteúdo habilitados, com seção "Outros plugins by Melk".
- Reaproveitamento da palavra-chave principal do Yoast SEO ou Rank Math (quando um deles está ativo) na primeira hashtag, via filtro `skbm_primary_keyword`; fallback para a primeira tag do post quando nenhum plugin de SEO está ativo. Aviso discreto na tela de configurações sugerindo um plugin de SEO, sem exigir nenhum.
