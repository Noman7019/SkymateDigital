import LegalPage, { LegalSection } from "@/components/legal/LegalPage";
import data from "@/data/privacy-live.json";

const PrivacyPolicy = () => (
  <LegalPage kicker="Legal" title="Privacy" accent="Policy"
    subtitle="How Sky Mate Digital collects, uses, and safeguards your personal information when you engage our Shopify store creation, branding, advertising, and growth services."
    sections={data as LegalSection[]} />
);
export default PrivacyPolicy;
