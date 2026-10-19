import LegalPage, { LegalSection } from "@/components/legal/LegalPage";
import data from "@/data/terms-live.json";

const Terms = () => (
  <LegalPage kicker="Legal" title="Terms of" accent="Service"
    subtitle="These Terms govern your access to our website and the e-commerce Services we provide, including Shopify store creation, branding, paid advertising, and store management. By using our Site or engaging our Services, you accept these Terms."
    sections={data as LegalSection[]} />
);
export default Terms;
