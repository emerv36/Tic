import fitz
import sys

def convert_pdf_to_images(pdf_path, prefix):
    doc = fitz.open(pdf_path)
    for page_num in range(len(doc)):
        page = doc.load_page(page_num)
        pix = page.get_pixmap(dpi=300)
        output_path = f"{prefix}_{page_num + 1}.png"
        pix.save(output_path)
        print(f"Saved {output_path}")

if __name__ == "__main__":
    convert_pdf_to_images("carnet estudainte.pdf", "assets/pdf/frente")
