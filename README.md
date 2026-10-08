# Social Kit by Melk

Plugin WordPress leve, eficiente e seguro que gera automaticamente — ao salvar um post — o rótulo, o título do card, o texto de apoio e a legenda já com o link, prontos para divulgar o conteúdo nas redes sociais (começando pelo X, com arquitetura pronta para outras redes).

## Por que existe

Nasceu como uma feature ("Social Kit Beta") dentro do [URL Shortener by Melk](https://github.com/Melksedeque/url-shortener), mas foi extraído antes de qualquer publicação: misturar "encurtar URL" com "gerar conteúdo social" confundia o escopo de um plugin focado só em links curtos. Agora é um produto próprio, com seu próprio roadmap.

## Recursos (Fase 1 — MVP)

- Geração automática (regras determinísticas, sem IA) ao salvar/publicar o post.
- Painel próprio no editor (Gutenberg) com contador de caracteres, botões de copiar e "Abrir no X".
- Edição manual de um campo trava a regeneração automática dele (`_skbm_locked`), sem perder o ajuste.
- Integração **opcional** com o [URL Shortener by Melk](https://github.com/Melksedeque/url-shortener): usa o link curto automaticamente se o outro plugin estiver ativo; senão, usa o permalink normal. Nenhuma dependência obrigatória.
- Rótulos, CTAs, hashtags e stopwords configuráveis via filtros `skbm_*` — nada hardcoded para um nicho.
- Opt-in por tipo de conteúdo (`Configurações > Social Kit`).

## Roadmap

| Fase | Entrega |
|---|---|
| 1.0 (atual) | Post meta + geração por regras + painel com copiar + contador + link curto opcional |
| 1.1 | Instagram (nova entrada em `class-social-config.php`) |
| 1.2 | Vínculo de contas (OAuth) + publicação automática via API |
| 1.3+ | Integração com Canva para gerar a arte do card |

## Requisitos

- WordPress 5.3+
- PHP 7.4+

## Licença

GPLv3 — veja [LICENSE](LICENSE).
