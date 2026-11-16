# 12. Exports and Interoperability

## 1. Export formats

Initial targets: - CSV: participant list, pairings, results,
standings. - PDF: printable pairing sheets, crosstable, standings. -
XLSX: organizer-friendly workbook. - TRF: FIDE tournament
report/interchange format, implemented against the current official
specification. - JSON: documented API snapshot for archival/recovery.

Do not claim TRF compatibility until validated with the current FIDE
specification and accepted downstream tools.

## 2. Export workflow

-   Exports are generated from a consistent database snapshot.
-   Large exports run in the queue.
-   Download links are authorized and short-lived.
-   Every export records creator, tournament, format, filters, and
    creation time.
-   Public exports contain only public fields.
-   Include the tournament ruleset and tie-break configuration in
    archival exports where appropriate.

## 3. CSV safety

-   Escape delimiters, quotes, and newlines.
-   Protect against spreadsheet formula injection for values beginning
    with `=`, `+`, `-`, or `@`.
-   Preserve UTF-8 names.
-   Include clear column headers.
-   Document score units and result codes.

## 4. PDF requirements

-   Clear tournament title, round, date, venue.
-   Legible board numbers and player names.
-   White/Black distinction that remains understandable in grayscale.
-   Page numbers and generation timestamp.
-   Optional arbiter signature area.
-   Avoid splitting one pairing row across pages.

## 5. TRF implementation gate

Before implementing TRF: 1. Obtain the current official format
specification. 2. Build parser and serializer fixtures. 3. Validate
round/result/bye/forfeit representation. 4. Round-trip supported
records. 5. Compare generated files with trusted tools. 6. Document
unsupported fields rather than silently dropping them.
