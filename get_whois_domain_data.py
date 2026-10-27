#!/home/admin/venv-lookup/bin/python
import sys
from whois import whois


def dedupe(text):
    out, prev = [], None
    for line in (text or "").splitlines():
        if line != prev:
            out.append(line)
        prev = line
    return "\n".join(out)


def html(text):
    return (
        dedupe(text)
        .replace("&", "&amp;")
        .replace("<", "&lt;")
        .replace(">", "&gt;")
        .replace("\n", "<br>")
    )


def main():
    if len(sys.argv) < 2:
        return

    try:
        w = whois(sys.argv[1])

        print(html(getattr(w, "text", "")))
        print("<br>------------------------------------<br>")
        print(html(str(w)))

    except Exception as e:
        print(f"ERROR: {e}")
        sys.exit(1)


if __name__ == "__main__":
    main()