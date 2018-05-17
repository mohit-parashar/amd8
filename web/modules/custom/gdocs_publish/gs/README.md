# Google Docs add-on

This module does nothing without the corresponding Google Docs add-on, the code for which is maintained here so that it's in sync with the module code (and also because Google provides no real version control for container-bound apps scripts.


## Contents

- `DrupalPublishForm.html`
  - This is the HTML for the **Publish to Drupal** dialog
- `DrupalPublish.js`
	- This is the source code from which the add on is created. This is *not* the apps script code itself.
- `DrupalPublish.gs`
  - The add-on code, _not_ including the Tags stack class. See **Creating a release** below.
- `Tags.js`
  - The stack class used for the HTML processing
- `ConvertGoogleDocToCleanHtml.js`
  - Original code maintained for reference but no longer used. (See below)

## Creating a release

The GAS code is managed using [`preprocessor.js`](https://www.npmjs.com/package/preprocessor). To create the production code:

	`preprocess DrupalPublish.js . > DrupalPublish.gs`

To create a version with debug logging enabled:

	`preprocess DrupalPublish.js . -DEBUG=true > DrupalPublish.gs`

## HTML Conversion

Converting a Google Doc to HTML should have been simple -- after all you can just download it as such. However, in an add-on, you can [get the document only as text](https://developers.google.com/apps-script/reference/document/text#getText%28%29) (no formatting) or as a PDF using [`Document.getAs()`](https://developers.google.com/apps-script/reference/document/document#getAs%28String%29), which returns a [`Blob`](https://developers.google.com/apps-script/reference/base/blob) which has a [`getAs` method](https://developers.google.com/apps-script/reference/base/blob#getAs%28String%29), which takes a string argument with the MIME type:

> For most blobs, 'application/pdf' is the only valid option. For images in BMP, GIF, JPEG, or PNG format, any of 'image/bmp', 'image/gif', 'image/jpeg', or 'image/png' are also valid.

There is an [open issue](https://code.google.com/p/google-apps-script-issues/issues/detail?id=585) asking for the obvious enhancement, but five years later nothing has changed. However the issue does have some workarounds. The best seems to be to use the document ID to get the file, but the [Drive Service](https://developers.google.com/apps-script/reference/drive/) API has the same limitations.

The only way to get the HTML version of the document is to peform a URL fetch to the export link, but then you find that the converted HTML is awful, almost worse than what most word processors produce. There is no structural markup whatsoever; even bold text is turned into a `<span>` with some arbitrary class name.

Therefore we turned to [GoogleDoc2Html
](https://github.com/oazabir/GoogleDoc2Html) to render the Google Document into HTML. However, this was also problematic as it attempts to process the document on its own terms (where there is, for instance, no such thing as a **list** element, just **list items** that may represent new lists, nested lists, or new items for an existing list.

The HTML conversion is now done using a rudimentary stack implementation that includes a lot of contextual and backtracking methods. Some of the original code is retained.

