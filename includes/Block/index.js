!(function () {
    "use strict";
    var t,
        e = window.wp.element,
        c = window.wp.i18n,
        l = window.wp.blocks,
        r = e.createElement(
            "svg",
            {
                width: "20px",
                height: "20px",
                viewBox: "0 0 24 24",
                xmlns: "http://www.w3.org/2000/svg",
            },
            e.createElement("path", {
                d: "M22 6H2a1.001 1.001 0 0 0-1 1v3a1.001 1.001 0 0 0 1 1h20a1.001 1.001 0 0 0 1-1V7a1.001 1.001 0 0 0-1-1zm0 4H2V7h20v3h.001M22 17H2a1.001 1.001 0 0 0-1 1v3a1.001 1.001 0 0 0 1 1h20a1.001 1.001 0 0 0 1-1v-3a1.001 1.001 0 0 0-1-1zm0 4H2v-3h20v3h.001M10 14v1H2v-1zM2 3h8v1H2z",
            })
        ),
        k = [
            (0, c.__)("form popup", "wpb-form-popup"),
            (0, c.__)("form", "wpb-form-popup"),
            (0, c.__)("forms", "wpb-form-popup"),
            (0, c.__)("popup", "wpb-form-popup"),
            (0, c.__)("cf7", "wpb-form-popup"),
            (0, c.__)("wpforms", "wpb-form-popup"),
            (0, c.__)("contact", "wpb-form-popup"),
            (0, c.__)("ninja", "wpb-form-popup"),
            (0, c.__)("formidable", "wpb-form-popup"),
            (0, c.__)("forminator", "wpb-form-popup"),
            (0, c.__)("weForms", "wpb-form-popup"),
        ],
        a = window.wp.compose,
        o = window.wp.components,
        n = {
            from: [
                {
                    type: "shortcode",
                    tag: "wpbean-fopo-form-popup",
                    attributes: {
                        id: {
                            type: "integer",
                            shortcode: (t) => {
                                let {
                                    named: { id: e },
                                } = t;
                                return parseInt(e);
                            },
                        },
                        title: {
                            type: "string",
                            shortcode: (t) => {
                                let {
                                    named: { title: e },
                                } = t;
                                return e;
                            },
                        },
                    },
                },
            ],
            to: [
                {
                    type: "block",
                    blocks: ["core/shortcode"],
                    transform: (t) =>
                        (0, l.createBlock)("core/shortcode", {
                            text: `[wpbean-fopo-form-popup id="${t.id}"]`,
                        }),
                },
            ],
        };
    (window.wpbfopo =
        null !== (t = window.wpbfopo) && void 0 !== t
            ? t
            : {
                ShortCodes: [],
            }),
        (0, l.registerBlockType)("wpb-form-popup/wpbean-fopo-shortcode-selector", {
            title: (0, c.__)("WPB Form Popup", "wpb-form-popup"),
            description: (0, c.__)(
                "Selecr a Popup ShortCode that you have created with our ShortCode builder.",
                "wpb-form-popup"
            ),
            category: "widgets",
            attributes: {
                id: {
                    type: "integer",
                },
                title: {
                    type: "string",
                },
            },
            icon: r,
            keywords: k,
            transforms: n,
            edit: function t(l) {
                let { attributes: r, setAttributes: n } = l;
                const i = new Map();
                //console.log( window.wpbfopo.ShortCodes );

                if (
                    (Object.entries(window.wpbfopo.ShortCodes).forEach((t) => {
                        let [e, c] = t;
                        i.set(c.id, c);
                    }),
                        !i.size && !r.id)
                )
                    return (0, e.createElement)(
                        "div",
                        {
                            className: "components-placeholder",
                        },
                        (0, e.createElement)(
                            "p",
                            null,
                            (0, c.__)(
                                "No ShortCodes were found. Create a ShortCode first.",
                                "wpb-form-popup"
                            )
                        )
                    );
                const s = Array.from(i.values(), (t) => ({
                    value: t.id,
                    label: t.title,
                }));
                if (r.id)
                    s.length ||
                        s.push({
                            value: r.id,
                            label: r.title,
                        });
                else {
                    const t = s[0];
                    r = {
                        id: parseInt(t.value),
                        title: t.label,
                    };
                }
                const m = `wpbean-fopo-shortcode-selector-${(0, a.useInstanceId)(t)}`;
                return (0, e.createElement)(
                    "div",
                    {
                        className: "components-placeholder",
                    },
                    (0, e.createElement)(
                        "label",
                        {
                            htmlFor: m,
                            className: "components-placeholder__label",
                        },
                        (0, c.__)("Select a Popup:", "wpb-form-popup")
                    ),
                    (0, e.createElement)(o.SelectControl, {
                        id: m,
                        options: s,
                        value: r.id,
                        onChange: (t) =>
                            n({
                                id: parseInt(t),
                                title: i.get(parseInt(t)).title,
                            }),
                    })
                );
            },
            save: (t) => {
                var c, l, r, a;
                let { attributes: o } = t;
                return (
                    (o = {
                        id:
                            null !== (c = o.id) && void 0 !== c
                                ? c
                                : null === (l = window.wpbfopo.ShortCodes[0]) || void 0 === l
                                    ? void 0
                                    : l.id,
                        title:
                            null !== (r = o.title) && void 0 !== r
                                ? r
                                : null === (a = window.wpbfopo.ShortCodes[0]) || void 0 === a
                                    ? void 0
                                    : a.title,
                    }),
                    (0, e.createElement)(
                        "div",
                        null,
                        '[wpbean-fopo-form-popup id="',
                        o.id,
                        '"]'
                    )
                );
            },
        });
})();
