# Assets do Social Kit by Melk (WordPress.org)

Estes arquivos vão para a pasta `assets/` do SVN (nunca para o plugin). A pasta `_source/` guarda a arte-fonte e **não é enviada**.
Este arquivo fica em `_source/` pelo mesmo motivo.

| Arquivo | Medida | Observação |
|---|---|---|
| `icon-128x128.png` | 128 x 128 | obrigatório para um bom visual na busca |
| `icon-256x256.png` | 256 x 256 | versão retina do ícone |
| `banner-772x250.png` | 772 x 250 | topo da página do plugin |
| `banner-1544x500.png` | 1544 x 500 | versão retina do banner |
| `screenshot-1.png` | livre | "Painel 'Social Kit' no editor, com os campos gerados e o contador de caracteres." |
| `screenshot-2.png` | livre | "Tela de configurações, com os tipos de post ativados." |

Regras:
- Um formato por asset (png **ou** jpg, nunca os dois do mesmo nome); o `wporg_release.py` valida nomes e dimensões.
- Os prints devem ser em **inglês** (a legenda do `readme.txt` é a fonte; o GlotPress traduz a legenda, não a imagem).
- A numeração dos screenshots segue a ordem da seção `== Screenshots ==` do `readme.txt`.
- Marca: seguir a identidade do URL Shortener by Melk (ver `url-shortener-by-melk/.wordpress-org/_source`) para a família de plugins ficar coerente.
- Ícone, banner e prints podem ser enviados **depois** da aprovação (via SVN); a revisão não exige.
