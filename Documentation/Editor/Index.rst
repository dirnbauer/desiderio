..  include:: /Includes.rst.txt

..  _editor:

===========
For editors
===========

Desiderio provides 255 editor-facing content elements. They are built as
TYPO3 Content Blocks and render through shared Fluid components, so form
fields, buttons, cards, badges, icons, charts, images, and dark mode use
one visual system.

..  _editor-content-elements:

Content elements
================

Elements are grouped by editorial task: heroes, features, conversion,
data, media, social proof, pricing, people, footers, and supporting page
sections.

Use the normal TYPO3 Page module to add and rearrange elements. Most
elements expose structured fields for image, headline, lead text, body
text, button label, link, icon, badge, and repeated items. Image fields
use TYPO3 FAL; the frontend templates render them with responsive Fluid
image ViewHelpers and ``object-fit`` rules.

..  _editor-icons:

Icons
=====

Icon fields store semantic icon keys, not library-specific names. A site
can switch from Tabler to Lucide, Phosphor, HugeIcons, or Remix Icon
through site settings and existing content keeps working.

..  _editor-data-and-code:

Data and code elements
======================

Chart elements include chart title, description, axis labels, units,
legend entries, and values. Editors should keep units explicit, for
example ``k EUR``, ``users``, or ``ms``.

Code examples are highlighted by the lightweight ``prism-lite.js`` asset.
Syntax colours are neutral and token-based; they do not use the site
accent colour.

..  _editor-forms:

Forms
=====

Desiderio styles TYPO3 Form Framework and Powermail forms with shared
shadcn primitives. Inputs use neutral theme borders by default and red
validation styling only for invalid fields. Required markers stay close
to their labels.

Friendly Captcha can be used for protected forms. Local development can
enable the Desiderio Friendly Captcha test mode; production must use real
Friendly Captcha keys.

TYPO3 Form Framework forms can also sync submitted data to Brevo when the
Brevo finisher is configured and ``BREVO_API_KEY`` is available in the
environment.

..  _editor-search:

Search
======

Header search can submit to the configured search result page. When the
Solr set is enabled, the same field also uses the JSON suggest endpoint
for live suggestions.

Search result pages use shadcn-styled pagination, result-count text, tabs,
and result cards. Editors configure the target page through site settings.

..  _editor-news-blog:

News and Blog
=============

When ``georgringer/news`` is installed and the Desiderio News set is
enabled, News list and detail views use the extension templates under
``Resources/Private/Extensions/News/``. Lists render images, categories,
tags, dates, teasers, and load-more behavior.

When ``t3g/blog`` is installed and the Desiderio Blog set is enabled,
Blog list and detail views use the templates under
``Resources/Private/Extensions/Blog/``. Blog templates keep the sidebar
widgets, metadata, categories, tags, author boxes, comments, related
posts, and RSS output.

..  _editor-workspaces:

Workspaces
==========

Desiderio is workspace-aware. Backend previews show draft content in the
active workspace. The styleguide seed command writes fixture records only
to the live workspace and refuses to run from an offline workspace.
