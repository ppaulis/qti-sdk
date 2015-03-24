<?php
namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\TextRun;
use qtism\data\content\InlineCollection;
use qtism\data\content\xhtml\text\P;
use qtism\data\ExtendedTestPart;
use qtism\data\TestFeedbackRefCollection;
use qtism\data\TestFeedbackCollection;
use qtism\data\rules\BranchRuleCollection;
use qtism\data\rules\PreConditionCollection;
use qtism\data\AssessmentSectionCollection;
use qtism\data\TestFeedbackRef;
use qtism\data\TestFeedbackAccess;
use qtism\data\ShowHide;
use qtism\data\TestFeedback;
use qtism\data\TimeLimits;
use qtism\data\ItemSessionControl;
use qtism\data\rules\BranchRule;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\rules\PreCondition;
use qtism\data\ExtendedAssessmentSection;
use qtism\common\datatypes\Duration;
use qtism\data\storage\xml\marshalling\CompactMarshallerFactory;
use \DOMDocument;

class ExtendedTestPartMarshallerTest extends QtiSmTestCase {

	public function testMarshallMaximal() {
	    $assessmentSection1 = new ExtendedAssessmentSection('section1', 'My Section 1', true);
	    $assessmentSection2 = new ExtendedAssessmentSection('section2', 'My Section 2', true);
	    
	    $preCondition = new PreCondition(new BaseValue(BaseType::BOOLEAN, true));
	    $branching = new BranchRule(new BaseValue(BaseType::BOOLEAN, true), 'EXIT_TESTPART');
	    
	    $itemSessionControl = new ItemSessionControl();
	    $itemSessionControl->setShowSolution(true);
	    
	    $timeLimits = new TimeLimits(null, new Duration('PT1M40S'));
	    $p = new P();
	    $p->setContent(new InlineCollection(array(new TextRun('Prima!'))));
	    
	    $testFeedback = new TestFeedback('feedback1', 'show', new FlowStaticCollection(array($p)));
	    $testFeedback->setTitle('hello!');
	    $testFeedback->setAccess(TestFeedbackAccess::AT_END);
	    $testFeedback->setShowHide(ShowHide::SHOW);
	    
	    $testFeedbackRef = new TestFeedbackRef('feedback1', 'show', TestFeedbackAccess::AT_END, ShowHide::SHOW, './TF01.xml');
	    
	    $assessmentSections = new AssessmentSectionCollection(array($assessmentSection1, $assessmentSection2));
	    $preConditions = new PreConditionCollection(array($preCondition));
	    $branchings = new BranchRuleCollection(array($branching));
	    $testFeedbacks = new TestFeedbackCollection(array($testFeedback));
	    $testFeedbackRefs = new TestFeedbackRefCollection(array($testFeedbackRef));
	    
	    $extendedTestPart = new ExtendedTestPart('part1', $assessmentSections);
	    $extendedTestPart->setPreConditions($preConditions);
	    $extendedTestPart->setBranchRules($branchings);
	    $extendedTestPart->setItemSessionControl($itemSessionControl);
	    $extendedTestPart->setTimeLimits($timeLimits);
	    $extendedTestPart->setTestFeedbacks($testFeedbacks);
	    $extendedTestPart->setTestFeedbackRefs($testFeedbackRefs);
	    
	    $factory = new CompactMarshallerFactory();
	    $element = $factory->createMarshaller($extendedTestPart)->marshall($extendedTestPart);
	    
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	    $this->assertEquals('<testPart identifier="part1" navigationMode="linear" submissionMode="individual"><preCondition><baseValue baseType="boolean">true</baseValue></preCondition><branchRule target="EXIT_TESTPART"><baseValue baseType="boolean">true</baseValue></branchRule><itemSessionControl maxAttempts="1" showFeedback="false" allowReview="true" showSolution="true" allowComment="false" allowSkipping="true" validateResponses="false"/><timeLimits maxTime="100" allowLateSubmission="false"/><assessmentSection identifier="section1" required="false" fixed="false" title="My Section 1" visible="true" keepTogether="true"/><assessmentSection identifier="section2" required="false" fixed="false" title="My Section 2" visible="true" keepTogether="true"/><testFeedback access="atEnd" outcomeIdentifier="show" showHide="show" identifier="feedback1" title="hello!"><p>Prima!</p></testFeedback><testFeedbackRef identifier="feedback1" outcomeIdentifier="show" access="atEnd" showHide="show" href="./TF01.xml"/></testPart>', $dom->saveXML($element));
	}
	
	public function testUnmarshallMaximal() {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML(
			'<testPart xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="part1" navigationMode="linear" submissionMode="individual">
		        <preCondition>
		            <baseValue baseType="boolean">true</baseValue>
		        </preCondition>
		        <branchRule target="EXIT_TESTPART">
		            <baseValue baseType="boolean">true</baseValue>
		        </branchRule>
		        <itemSessionControl showSolution="true"/>
		        <timeLimits maxTime="100"/>
				<assessmentSection identifier="section1" title="My Section 1" visible="true"/>
				<assessmentSection identifier="section2" title="My Section 2" visible="false"/>
		        <testFeedback outcomeIdentifier="feedback1" identifier="show" showHide="show" title="hello!" access="atEnd">
		            <p>Prima!</p>
		        </testFeedback>
		        <testFeedbackRef outcomeIdentifier="feedback2" identifier="show" showHide="show" access="atEnd" href="./TF01.xml"/>
			</testPart>');
		
		$element = $dom->documentElement;
		$factory = new CompactMarshallerFactory();
		
		$marshaller = $factory->createMarshaller($element);
		$component = $marshaller->unmarshall($element);
		
		$this->assertInstanceOf('qtism\\data\\ExtendedTestPart', $component);
		$this->assertEquals(1, count($component->getPreConditions()));
		$this->assertEquals(1, count($component->getBranchRules()));
		$this->assertTrue($component->getItemSessionControl()->mustShowSolution());
		$this->assertTrue($component->getTimeLimits()->getMaxTime()->equals(new Duration('PT1M40S')));
		$this->assertEquals(1, count($component->getTestFeedbacks()));
		$this->assertEquals(1, count($component->getTestFeedbackRefs()));
	    $this->assertEquals(2, count($component->getAssessmentSections()));
	    
	    // Check that we got ExtendedAssessmentSections.
	    $assessmentSections = $component->getAssessmentSections();
	    $this->assertInstanceOf('qtism\\data\\ExtendedAssessmentSection', $assessmentSections['section1']);
	    $this->assertInstanceOf('qtism\\data\\ExtendedAssessmentSection', $assessmentSections['section2']);
	    
	    // Check that we got TestFeedbackRef instances.
	    $testFeedbackRefs = $component->getTestFeedbackRefs();
	    $this->assertInstanceOf('qtism\\data\\TestFeedbackRef', $testFeedbackRefs[0]);
	}
}